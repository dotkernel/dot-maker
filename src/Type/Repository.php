<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Parameter;
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
                $this->component(TypeEnum::Entity)->create($name);
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
            $repository->getComponent(),
            $this->fileSystem->entity($name)->getComponent(),
        );

        $repository->create($content);

        Output::success(sprintf('Created Repository "%s"', $repository->getPath()));

        return $repository;
    }

    public function render(Component $repository, Component $entity): string
    {
        $class = (new ClassFile($repository->getNamespace(), $repository->getClassName()))
            ->setExtends('AbstractRepository')
            ->useClass($this->import->getAbstractRepositoryFqcn())
            ->useClass($entity->getFqcn())
            ->useClass(Import::DOCTRINE_ORM_QUERYBUILDER)
            ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_ENTITY)
            ->addInject(
                (new Inject('Entity'))->addArgument($entity->getClassString(), 'name')
            );

        $getResources = (new Method($entity->getCollectionMethodName()))
            ->setReturnType('QueryBuilder')
            ->addParameter(
                new Parameter('params', 'array', false, '[]')
            )
            ->addParameter(
                new Parameter('filters', 'array', false, '[]')
            )
            ->setComment(<<<COMM
/**
     * @param array<non-empty-string, mixed> \$params
     * @param array<non-empty-string, mixed> \$filters
     */
COMM)
            ->setBody(<<<BODY
        \$queryBuilder = \$this
            ->getQueryBuilder()
            ->select(['{$entity->toCamelCase()}'])
            ->from({$entity->getClassString()}, '{$entity->toCamelCase()}');

        // add filters

        \$queryBuilder
            ->orderBy(\$params['sort'], \$params['dir'])
            ->setFirstResult(\$params['offset'])
            ->setMaxResults(\$params['limit'])
            ->groupBy('{$entity->toCamelCase()}.uuid');
        \$queryBuilder->getQuery()->useQueryCache(true);

        return \$queryBuilder;
BODY);
        $class->addMethod($getResources);

        return $class->render();
    }
}
