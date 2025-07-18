<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\Import;
use Dot\Maker\ContextInterface;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;

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

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid Repository name: "%s"', $name));
                continue;
            }

            $repository = $this->fileSystem->repository($name);
            if ($repository->exists()) {
                Output::error(
                    sprintf(
                        'Repository "%s" already exists at %s',
                        $repository->getComponent()->getClassName(),
                        $repository->getPath()
                    )
                );
                continue;
            }

            $entity = $this->fileSystem->entity($name);

            $repository
                ->ensureParentDirectoryExists()
                ->getComponent()
                    ->useClass($this->getAbstractRepositoryFqcn())
                    ->useClass($entity->getComponent()->getFqcn())
                    ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_ENTITY);

            if (! $repository->create($this->render($repository->getComponent(), $entity->getComponent()))) {
                Output::error(sprintf('Could not create Repository "%s"', $repository->getPath()), true);
            }
            Output::info(sprintf('Created new Repository "%s"', $repository->getPath()));

            $this->initComponent(TypeEnum::Entity)->create($name);

            break;
        }
    }

    public function create(string $name): ?File
    {
        if (! $this->isValid($name)) {
            Output::error(sprintf('Invalid Repository name: "%s"', $name), true);
        }

        $repository = $this->fileSystem->repository($name);
        if ($repository->exists()) {
            Output::error(
                sprintf(
                    'Repository "%s" already exists at %s',
                    $repository->getComponent()->getClassName(),
                    $repository->getPath()
                ),
                true
            );
        }

        $entity = $this->fileSystem->entity($name);

        $repository
            ->ensureParentDirectoryExists()
            ->getComponent()
                ->useClass($this->getAbstractRepositoryFqcn())
                ->useClass($entity->getComponent()->getFqcn())
                ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_ENTITY);

        if (! $repository->create($this->render($repository->getComponent(), $entity->getComponent()))) {
            Output::error(sprintf('Could not create Repository "%s"', $repository->getPath()), true);
        }
        Output::info(sprintf('Created new Repository "%s"', $repository->getPath()));

        return $repository;
    }

    public function render(Component $repository, Component $entity): string
    {
        return $this->stub->render('repository.stub', [
            'ENTITY_CLASS_STRING'   => $entity->getClassString(),
            'REPOSITORY_CLASS_NAME' => $repository->getClassName(),
            'REPOSITORY_NAMESPACE'  => $repository->getNamespace(),
            'USES'                  => $repository->getImport()->render(),
        ]);
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
