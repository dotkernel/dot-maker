<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Parameter;
use Dot\Maker\ContextInterface;
use Dot\Maker\Exception\BadRequestException;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Throwable;

use function sprintf;
use function ucfirst;

class Repository extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Repository name: '));
            if ($name === '') {
                return;
            }

            try {
                $this->create($name);
                $this->initComponent(TypeEnum::Entity)->create($name);
                break;
            } catch (Throwable $exception) {
                Output::error($exception->getMessage());
            }
        }
    }

    /**
     * @throws BadRequestException
     * @throws DuplicateFileException
     * @throws RuntimeException
     */
    public function create(string $name): File
    {
        if (! $this->isValid($name)) {
            throw new BadRequestException(sprintf('Invalid Repository name: "%s"', $name));
        }

        $repository = $this->fileSystem->repository($name);
        if ($repository->exists()) {
            throw DuplicateFileException::create($repository);
        }

        $content = $this->render(
            $repository,
            $this->fileSystem->entity($name)
        );

        try {
            $repository->create($content);
            Output::info(sprintf('Created Repository "%s"', $repository->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $repository;
    }

    public function render(File $repository, File $entity): string
    {
        $class = (new ClassFile(
            $repository->getComponent()->getNamespace(),
            $repository->getComponent()->getClassName()
        ))
            ->setExtends('AbstractRepository')
            ->useClass($this->getAbstractRepositoryFqcn())
            ->useClass($entity->getComponent()->getFqcn())
            ->useClass(Import::DOCTRINE_ORM_QUERYBUILDER)
            ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_ENTITY)
            ->addInject(
                (new Inject('Entity'))->addArgument($entity->getComponent()->getClassString(), 'name')
            );

        $getResources = (new Method($entity->getComponent()->getCollectionMethodName()))
            ->addParameter(
                new Parameter('params', 'array', false, '[]')
            )
            ->addParameter(
                new Parameter('filters', 'array', false, '[]')
            )
            ->setReturnType('QueryBuilder')
            ->setBody(<<<BODY
        \$queryBuilder = \$this
            ->getQueryBuilder()
            ->select(['{$entity->getComponent()->getPropertyName()}'])
            ->from({$entity->getComponent()->getClassString()}, '{$entity->getComponent()->getPropertyName()}');

        \$queryBuilder
            ->orderBy(\$params['sort'], \$params['dir'])
            ->setFirstResult(\$params['offset'])
            ->setMaxResults(\$params['limit'])
            ->groupBy('{$entity->getComponent()->getPropertyName()}.uuid');
        \$queryBuilder->getQuery()->useQueryCache(true);

        return \$queryBuilder;
BODY);
        $class->addMethod($getResources);

        return $class->render();
    }

    public function getAbstractRepositoryFqcn(): string
    {
        $format = Import::ROOT_APP_REPOSITORY_ABSTRACTREPOSITORY;

        if ($this->context->hasCore()) {
            return sprintf($format, ContextInterface::NAMESPACE_CORE);
        }

        return sprintf($format, $this->context->getRootNamespace());
    }
}
