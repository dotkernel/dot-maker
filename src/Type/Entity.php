<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Method\Constructor;
use Dot\Maker\ContextInterface;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;

use function implode;
use function preg_split;
use function sprintf;
use function strtolower;
use function ucfirst;

use const PHP_EOL;

class Entity extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Entity name: '));
            if ($name === '') {
                return;
            }

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid Entity name: "%s"', $name));
                continue;
            }

            $entity = $this->fileSystem->entity($name);
            if ($entity->exists()) {
                Output::error(
                    sprintf(
                        'Entity "%s" already exists at %s',
                        $entity->getComponent()->getClassName(),
                        $entity->getPath()
                    )
                );
                continue;
            }

            $repository = $this->fileSystem->repository($name);

            $entity
                ->ensureParentDirectoryExists()
                ->getComponent()
                    ->useClass($repository->getComponent()->getFqcn())
                    ->useClass($this->getAbstractEntityFqcn())
                    ->useClass($this->getTimestampsTraitFqcn())
                    ->useClass(Import::DOCTRINE_ORM_MAPPING, 'ORM');

            $content = $this->render($entity->getComponent(), $repository->getComponent());
            if (! $entity->create($content)) {
                Output::error(sprintf('Could not create Entity "%s"', $entity->getPath()), true);
            }
            Output::info(sprintf('Created new Entity "%s"', $entity->getPath()));

            $this->initComponent(TypeEnum::Repository)->create($name);

            break;
        }
    }

    public function create(string $name): File
    {
        if (! $this->isValid($name)) {
            Output::error(sprintf('Invalid Entity name: "%s"', $name), true);
        }

        $entity = $this->fileSystem->entity($name);
        if ($entity->exists()) {
            Output::error(
                sprintf(
                    'Entity "%s" already exists at %s',
                    $entity->getComponent()->getClassName(),
                    $entity->getPath()
                ),
                true
            );
        }

        $repository = $this->fileSystem->repository($name);

        $entity
            ->ensureParentDirectoryExists()
            ->getComponent()
                ->useClass($repository->getComponent()->getFqcn())
                ->useClass($this->getAbstractEntityFqcn())
                ->useClass($this->getTimestampsTraitFqcn())
                ->useClass(Import::DOCTRINE_ORM_MAPPING, 'ORM');

        $content = $this->render($entity->getComponent(), $repository->getComponent());
        if (! $entity->create($content)) {
            Output::error(sprintf('Could not create Entity "%s"', $entity->getPath()), true);
        }
        Output::info(sprintf('Created new Entity "%s"', $entity->getPath()));

        return $entity;
    }

    public function render(Component $entity, Component $repository): string
    {
        $methods = [];

        $methods[] = (new Constructor())->setBody(<<<BODY
        parent::__construct();

        \$this->created();
BODY);

        $methods[] = (new Method('getArrayCopy'))
            ->setReturnType('array')
            ->setBody(<<<BODY
        return [
            'uuid'    => \$this->uuid->toString(),
            'created' => \$this->created,
            'updated' => \$this->updated,
        ];
BODY);

        return $this->stub->render('entity.stub', [
            'ENTITY_CLASS_NAME'       => $entity->getClassName(),
            'ENTITY_NAMESPACE'        => $entity->getNamespace(),
            'ENTITY_TABLE'            => $this->getTableName($entity->getClassName()),
            'REPOSITORY_CLASS_STRING' => $repository->getClassString(),
            'METHODS'                 => implode(PHP_EOL . PHP_EOL . '    ', $methods),
            'USES'                    => $entity->getImport()->render(),
        ]);
    }

    public function getAbstractEntityFqcn(): string
    {
        $format = Import::ROOT_APP_ENTITY_ABSTRACTENTITY;

        if ($this->context->hasCore()) {
            return sprintf($format, ContextInterface::NAMESPACE_CORE);
        }

        return sprintf($format, $this->context->getRootNamespace());
    }

    public function getTimestampsTraitFqcn(): string
    {
        $format = Import::ROOT_APP_ENTITY_TIMESTAMPSTRAIT;

        if ($this->context->hasCore()) {
            return sprintf($format, ContextInterface::NAMESPACE_CORE);
        }

        return sprintf($format, $this->context->getRootNamespace());
    }

    public function getTableName(string $name): string
    {
        $parts = preg_split('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', $name);

        return strtolower(implode('_', $parts));
    }
}
