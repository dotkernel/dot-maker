<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Method;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Output;
use Dot\Maker\Message;
use Dot\Maker\VisibilityEnum;
use Throwable;

use function sprintf;

class CoreConfigProvider extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        try {
            $this->create('ConfigProvider');
        } catch (Throwable $exception) {
            Output::error($exception->getMessage());
        }
    }

    /**
     * @throws DuplicateFileException
     * @throws RuntimeException
     */
    public function create(string $name): File
    {
        $configProvider = $this->fileSystem->coreConfigProvider();
        if ($configProvider->exists()) {
            throw DuplicateFileException::create($configProvider);
        }

        $content = $this->render(
            $configProvider->getComponent(),
            $this->fileSystem->repository($name)->getComponent(),
            $this->fileSystem->entity($name)->getComponent(),
        );

        $configProvider->create($content);

        $this
            ->addMessage(Message::addCoreConfigProviderToConfig($configProvider->getComponent()->getFqcn()))
            ->addMessage(Message::addCoreModuleToComposer($this->fileSystem->getModuleName()));

        Output::success(sprintf('Created Core ConfigProvider "%s"', $configProvider->getPath()));

        return $configProvider;
    }

    public function render(
        Component $configProvider,
        Component $repository,
        Component $entity,
    ): string {
        $class = (new ClassFile($configProvider->getNamespace(), $configProvider->getClassName()))
            ->useClass(Import::DOCTRINE_ORM_MAPPING_DRIVER_ATTRIBUTEDRIVER)
            ->useClass(Import::DOCTRINE_PERSISTENCE_MAPPING_DRIVER_MAPPINGDRIVER)
            ->useClass(Import::DOT_DEPENDENCYINJECTION_FACTORY_ATTRIBUTEDREPOSITORYFACTORY)
            ->useClass($repository->getFqcn())
            ->setComment(<<<COMM
/**
 * @phpstan-type ConfigType array{
 *      dependencies: DependenciesType,
 *      doctrine: DoctrineConfigType,
 * }
 * @phpstan-type DoctrineConfigType array{
 *      driver: array{
 *          orm_default: array{
 *              drivers: array<non-empty-string, non-empty-string>,
 *          },
 *          {$entity->getClassName()}Entities: array{
 *              class: class-string<MappingDriver>,
 *              cache: non-empty-string,
 *              paths: non-empty-string[],
 *          },
 *      },
 * }
 * @phpstan-type DependenciesType array{
 *       factories: array<class-string, class-string>,
 * }
 */
COMM);

        $invoke = (new Method('__invoke'))
            ->setReturnType('array')
            ->setComment(<<<COMM
/**
     * @return ConfigType
     */
COMM)
            ->setBody(<<<BODY
        return [
            'dependencies' => \$this->getDependencies(),
            'doctrine'     => \$this->getDoctrineConfig(),
        ];
BODY);
        $class->addMethod($invoke);

        $getDependencies = (new Method('getDependencies'))
            ->setReturnType('array')
            ->setVisibility(VisibilityEnum::Private)
            ->setComment(<<<COMM
/**
     * @return DependenciesType
     */
COMM)
            ->setBody(<<<BODY
        return [
            'factories' => [
                {$repository->getClassString()} => AttributedRepositoryFactory::class,
            ],
        ];
BODY);
        $class->addMethod($getDependencies);

        // phpcs:disable Generic.Files.LineLength.TooLong
        $getDoctrineConfig = (new Method('getDoctrineConfig'))
            ->setReturnType('array')
            ->setVisibility(VisibilityEnum::Private)
            ->setComment(<<<COMM
/**
     * @return DoctrineConfigType
     */
COMM)
            ->setBody(<<<BODY
        return [
            'driver' => [
                'orm_default' => [
                    'drivers' => [
                        '{$this->fileSystem->getModuleName()}\\{$entity->getClassName()}\\Entity' => '{$entity->getClassName()}Entities',
                    ],
                ],
                '{$entity->getClassName()}Entities' => [
                    'class' => AttributeDriver::class,
                    'cache' => 'array',
                    'paths' => [__DIR__ . '/Entity'],
                ],
            ],
        ];
BODY);
        $class->addMethod($getDoctrineConfig);
        // phpcs:enable Generic.Files.LineLength.TooLong

        return $class->render();
    }
}
