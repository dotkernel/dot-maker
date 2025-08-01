<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Method\Constructor;
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

class Entity extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Entity name: '));
            if ($name === '') {
                return;
            }

            try {
                $this->create($name);
                $this->initComponent(TypeEnum::Repository)->create($name);
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
            throw new BadRequestException(sprintf('Invalid Entity name: "%s"', $name));
        }

        $entity = $this->fileSystem->entity($name);
        if ($entity->exists()) {
            throw DuplicateFileException::create($entity);
        }

        $content = $this->render(
            $entity->getComponent(),
            $this->fileSystem->repository($name)->getComponent(),
        );

        try {
            $entity->create($content);
            Output::info(sprintf('Created Entity "%s"', $entity->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $entity;
    }

    public function render(Component $entity, Component $repository): string
    {
        $class = (new ClassFile($entity->getNamespace(), $entity->getClassName()))
            ->setExtends('AbstractEntity')
            ->useClass($this->getAbstractEntityFqcn())
            ->useClass($this->getTimestampsTraitFqcn())
            ->useClass($repository->getFqcn())
            ->useClass(Import::DOCTRINE_ORM_MAPPING, 'ORM')
            ->addInject(
                (new Inject('ORM\Entity'))->addArgument($repository->getClassString(), 'repositoryClass')
            )
            ->addInject(
                (new Inject('ORM\Table'))
                    ->addArgument(self::wrap($entity->toSnakeCase()), 'name')
            )
            ->addInject(
                new Inject('ORM\HasLifecycleCallbacks')
            )
            ->addTrait('TimestampsTrait');

        $constructor = (new Constructor())->setBody(<<<BODY
        parent::__construct();

        \$this->created();
BODY);
        $class->addMethod($constructor);

        $getArrayCopy = (new Method('getArrayCopy'))
            ->setReturnType('array')
            ->setBody(<<<BODY
        return [
            'uuid'    => \$this->uuid->toString(),
            'created' => \$this->created,
            'updated' => \$this->updated,
        ];
BODY);
        $class->addMethod($getArrayCopy);

        return $class->render();
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
}
