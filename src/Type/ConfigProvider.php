<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Method;
use Dot\Maker\ContextInterface;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Output;
use Dot\Maker\VisibilityEnum;
use Throwable;

use function sprintf;

class ConfigProvider extends AbstractType implements FileInterface
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
        $configProvider = $this->fileSystem->configProvider();
        if ($configProvider->exists()) {
            throw DuplicateFileException::create($configProvider);
        }

        if ($this->context->isApi()) {
            $content = $this->renderApi(
                $configProvider->getComponent(),
                $this->fileSystem->collection($name),
                $this->fileSystem->command($name),
                $this->fileSystem->entity($name),
                $this->fileSystem->middleware($name),
                $this->fileSystem->service($name),
                $this->fileSystem->serviceInterface($name),
                [
                    $this->fileSystem->apiDeleteResourceHandler($name),
                    $this->fileSystem->apiGetResourceHandler($name),
                    $this->fileSystem->apiGetCollectionHandler($name),
                    $this->fileSystem->apiPatchResourceHandler($name),
                    $this->fileSystem->apiPostResourceHandler($name),
                    $this->fileSystem->apiPutResourceHandler($name),
                ],
            );
        } else {
            $content = $this->render(
                $configProvider->getComponent(),
                $this->fileSystem->command($name),
                $this->fileSystem->entity($name),
                $this->fileSystem->middleware($name),
                $this->fileSystem->service($name),
                $this->fileSystem->serviceInterface($name),
                [
                    $this->fileSystem->getCreateResourceHandler($name),
                    $this->fileSystem->postCreateResourceHandler($name),
                    $this->fileSystem->getDeleteResourceHandler($name),
                    $this->fileSystem->postDeleteResourceHandler($name),
                    $this->fileSystem->getEditResourceHandler($name),
                    $this->fileSystem->postEditResourceHandler($name),
                    $this->fileSystem->getListResourcesHandler($name),
                    $this->fileSystem->getViewResourceHandler($name),
                ],
                [
                    $this->fileSystem->createResourceForm($name),
                    $this->fileSystem->deleteResourceForm($name),
                    $this->fileSystem->editResourceForm($name),
                ],
            );
        }

        try {
            $configProvider->create($content);
            Output::info(sprintf('Created ConfigProvider "%s"', $configProvider->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $configProvider;
    }

    /**
     * @param File[] $handlers
     */
    public function render(
        Component $configProvider,
        File $command,
        File $entity,
        File $middleware,
        File $service,
        File $serviceInterface,
        array $handlers,
        array $forms,
    ): string {
        $class = (new ClassFile($configProvider->getNamespace(), $configProvider->getClassName()))
            ->useClass(Import::DOT_DEPENDENCYINJECTION_FACTORY_ATTRIBUTEDSERVICEFACTORY)
            ->useClass(Import::MEZZIO_APPLICATION);

        $invoke = (new Method('__invoke'))
            ->setReturnType('array')
            ->setBody(<<<BODY
        return [
            'dependencies' => \$this->getDependencies(),
            'templates'    => \$this->getTemplates(),
        ];
BODY);
        $class->addMethod($invoke);

        $getDependencies = (new Method('getDependencies'))
            ->setReturnType('array')
            ->setVisibility(VisibilityEnum::Private)
            ->appendBody('return [')
            ->appendBody('\'delegators\' => [', 12)
            ->appendBody('Application::class => [RoutesDelegator::class],', 16)
            ->appendBody('],', 12)
            ->appendBody('\'factories\' => [', 12);

        if ($command->exists()) {
            $class->useClass($command->getComponent()->getFqcn());

            $getDependencies->appendBody(
                sprintf(
                    '%s => AttributedServiceFactory::class,',
                    $command->getComponent()->getClassString()
                ),
                16
            );
        }

        foreach ($handlers as $handler) {
            if (! $handler->exists()) {
                continue;
            }
            $class->useClass($handler->getComponent()->getFqcn());

            $getDependencies->appendBody(
                sprintf(
                    '%s => AttributedServiceFactory::class,',
                    $handler->getComponent()->getClassString()
                ),
                16
            );
        }

        foreach ($forms as $form) {
            if (! $form->exists()) {
                continue;
            }
            $class->useClass($form->getComponent()->getFqcn());

            $getDependencies->appendBody(
                sprintf(
                    '%s => AttributedServiceFactory::class,',
                    $form->getComponent()->getClassString()
                ),
                16
            );
        }

        if ($middleware->exists()) {
            $class->useClass($middleware->getComponent()->getFqcn());

            $getDependencies->appendBody(
                sprintf(
                    '%s => AttributedServiceFactory::class,',
                    $middleware->getComponent()->getClassString()
                ),
                16
            );
        }

        if ($service->exists()) {
            $class->useClass($service->getComponent()->getFqcn());

            $getDependencies->appendBody(
                sprintf(
                    '%s => AttributedServiceFactory::class,',
                    $service->getComponent()->getClassString()
                ),
                16
            );
        }

        $getDependencies->appendBody('],', 12);
        if ($serviceInterface->exists()) {
            $class->useClass($serviceInterface->getComponent()->getFqcn());

            $getDependencies
                ->appendBody('\'aliases\'    => [', 12)
                ->appendBody(
                    sprintf(
                        '%s => %s,',
                        $serviceInterface->getComponent()->getClassString(),
                        $service->getComponent()->getClassString()
                    ),
                    16
                )
                ->appendBody('],', 12);
        }
        $getDependencies->appendBody('];');
        $class->addMethod($getDependencies);

        // phpcs:disable Generic.Files.LineLength.TooLong
        $templates = (new Method('getTemplates'))
            ->setVisibility(VisibilityEnum::Private)
            ->setReturnType('array')
            ->setBody(<<<BODY
        return [
            'paths' => [
                '{$entity->getComponent()->toKebabCase()}' => [__DIR__ . '/../templates/{$entity->getComponent()->toKebabCase()}'],
            ],
        ];
BODY);
        $class->addMethod($templates);
        // phpcs:enable Generic.Files.LineLength.TooLong

        return $class->render();
    }

    /**
     * @param File[] $handlers
     */
    public function renderApi(
        Component $configProvider,
        File $collection,
        File $command,
        File $entity,
        File $middleware,
        File $service,
        File $serviceInterface,
        array $handlers
    ): string {
        $class = (new ClassFile($configProvider->getNamespace(), $configProvider->getClassName()))
            ->useClass(Import::getHandlerDelegatorFactoryFqcn($this->context->getRootNamespace()))
            ->useClass(Import::DOT_DEPENDENCYINJECTION_FACTORY_ATTRIBUTEDSERVICEFACTORY)
            ->useClass(Import::MEZZIO_APPLICATION)
            ->useClass(Import::MEZZIO_HAL_METADATA_METADATAMAP)
            ->useClass($this->getAppConfigProviderFqcn(), 'AppConfigProvider');

        $invoke = (new Method('__invoke'))
            ->setReturnType('array')
            ->setBody(<<<BODY
        return [
            'dependencies'     => \$this->getDependencies(),
            MetadataMap::class => \$this->getHalConfig(),
        ];
BODY);
        $class->addMethod($invoke);

        $getDependencies = (new Method('getDependencies'))
            ->setReturnType('array')
            ->setVisibility(VisibilityEnum::Private)
            ->appendBody('return [')
            ->appendBody('\'delegators\' => [', 12)
            ->appendBody('Application::class => [RoutesDelegator::class],', 16);

        foreach ($handlers as $handler) {
            if (! $handler->exists()) {
                continue;
            }
            $class->useClass($handler->getComponent()->getFqcn());

            $getDependencies->appendBody(
                sprintf(
                    '%s => [HandlerDelegatorFactory::class],',
                    $handler->getComponent()->getClassString()
                ),
                16
            );
        }

        $getDependencies
            ->appendBody('],', 12)
            ->appendBody('\'factories\'  => [', 12);

        if ($command->exists()) {
            $class->useClass($command->getComponent()->getFqcn());

            $getDependencies->appendBody(
                sprintf(
                    '%s => AttributedServiceFactory::class,',
                    $command->getComponent()->getClassString()
                ),
                16
            );
        }

        foreach ($handlers as $handler) {
            if (! $handler->exists()) {
                continue;
            }
            $class->useClass($handler->getComponent()->getFqcn());

            $getDependencies->appendBody(
                sprintf(
                    '%s => AttributedServiceFactory::class,',
                    $handler->getComponent()->getClassString()
                ),
                16
            );
        }

        if ($middleware->exists()) {
            $class->useClass($middleware->getComponent()->getFqcn());

            $getDependencies->appendBody(
                sprintf(
                    '%s => AttributedServiceFactory::class,',
                    $middleware->getComponent()->getClassString()
                ),
                16
            );
        }

        if ($service->exists()) {
            $class->useClass($service->getComponent()->getFqcn());

            $getDependencies->appendBody(
                sprintf(
                    '%s => AttributedServiceFactory::class,',
                    $service->getComponent()->getClassString()
                ),
                16
            );
        }

        $getDependencies->appendBody('],', 12);
        if ($serviceInterface->exists()) {
            $class->useClass($serviceInterface->getComponent()->getFqcn());

            $getDependencies
                ->appendBody('\'aliases\'    => [', 12)
                ->appendBody(
                    sprintf(
                        '%s => %s,',
                        $serviceInterface->getComponent()->getClassString(),
                        $service->getComponent()->getClassString()
                    ),
                    16
                )
                ->appendBody('],', 12);
        }
        $getDependencies->appendBody('];');
        $class->addMethod($getDependencies);

        $getHalConfig = (new Method('getHalConfig'))
            ->setReturnType('array')
            ->setVisibility(VisibilityEnum::Private);
        if ($collection->exists() && $entity->exists()) {
            $class
                ->useClass($collection->getComponent()->getFqcn())
                ->useClass($entity->getComponent()->getFqcn());

            $getHalConfig
                ->appendBody('return [')
                ->appendBody(
                    sprintf(
                        'AppConfigProvider::getCollection(%s, \'%s::list-%s\', \'%s\'),',
                        $collection->getComponent()->getClassString(),
                        $entity->getComponent()->toKebabCase(),
                        $entity->getComponent()->toKebabCase(),
                        Component::pluralize($entity->getComponent()->getClassName())
                    ),
                    12
                )
                ->appendBody(
                    sprintf(
                        'AppConfigProvider::getResource(%s, \'%s::view-%s\'),',
                        $entity->getComponent()->getClassString(),
                        $entity->getComponent()->toKebabCase(),
                        $entity->getComponent()->toKebabCase()
                    ),
                    12
                )
                ->appendBody('];');
        } else {
            $getHalConfig->appendBody('return [];');
        }
        $class->addMethod($getHalConfig);

        return $class->render();
    }

    public function getAppConfigProviderFqcn(bool $core = false): string
    {
        if ($core) {
            $rootNamespace = ContextInterface::NAMESPACE_CORE;
        } else {
            $rootNamespace = $this->context->getRootNamespace();
        }

        return sprintf(Import::ROOT_APP_CONFIGPROVIDER, $rootNamespace);
    }
}
