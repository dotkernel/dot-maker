<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Interface\Declaration;
use Dot\Maker\Component\InterfaceFile;
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

class ServiceInterface extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new ServiceInterface name: '));
            if ($name === '') {
                break;
            }

            try {
                $this->create($name);
                $this->initComponent(TypeEnum::Service)->create($name);
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
            throw new BadRequestException(sprintf('Invalid ServiceInterface name: "%s"', $name));
        }

        $serviceInterface = $this->fileSystem->serviceInterface($name);
        if ($serviceInterface->exists()) {
            throw DuplicateFileException::create($serviceInterface);
        }

        $content = $this->render(
            $serviceInterface->getComponent(),
            $this->fileSystem->repository($name)->getComponent(),
            $this->fileSystem->entity($name)->getComponent(),
        );

        try {
            $serviceInterface->create($content);
            Output::info(sprintf('Created ServiceInterface "%s"', $serviceInterface->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $serviceInterface;
    }

    public function render(Component $serviceInterface, Component $repository, Component $entity): string
    {
        $interface = (new InterfaceFile($serviceInterface->getNamespace(), $serviceInterface->getClassName()))
            ->useClass($repository->getFqcn())
            ->useClass($entity->getFqcn())
            ->addDeclaration(
                (new Declaration($repository->getGetterName()))
                    ->setReturnType($repository->getClassName())
            )
            ->addDeclaration(
                (new Declaration($entity->getDeleteMethodName()))
                    ->addParameter(
                        new Parameter($entity->toCamelCase(), $entity->getClassName())
                    )
            )
            ->addDeclaration(
                (new Declaration($entity->getCollectionMethodName()))
                    ->setReturnType($this->context->isApi() ? 'QueryBuilder' : 'array')
                    ->addParameter(
                        new Parameter('params', 'array')
                    )
            )
            ->addDeclaration(
                (new Declaration($entity->getSaveMethodName()))
                    ->setReturnType($entity->getClassName())
                    ->addParameter(
                        new Parameter('data', 'array')
                    )
                    ->addParameter(
                        new Parameter(
                            $entity->toCamelCase(),
                            $entity->getClassName(),
                            true,
                            'null'
                        )
                    )
            );

        if (! $this->context->isApi()) {
            $interface
                ->useClass($this->import->getNotFoundExceptionFqcn())
                ->addDeclaration(
                    (new Declaration($entity->getFindMethodName()))
                        ->setReturnType($entity->getClassName())
                        ->addParameter(
                            new Parameter('uuid', 'string')
                        )
                        ->setComment(<<<COMM
/**
     * @throws NotFoundException
     */
COMM)
                );
        } else {
            $interface->useClass(Import::DOCTRINE_ORM_QUERYBUILDER);
        }

        return $interface->render();
    }
}
