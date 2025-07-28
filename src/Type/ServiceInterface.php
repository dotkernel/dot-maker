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

        $serviceInterface->ensureParentDirectoryExists();

        $repository = $this->fileSystem->repository($name);
        $entity     = $this->fileSystem->entity($name);

        $content = $this->render(
            $serviceInterface->getComponent(),
            $repository->exists() ? $repository->getComponent() : null,
            $entity->exists() ? $entity->getComponent() : null,
        );
        if (! $serviceInterface->create($content)) {
            throw new RuntimeException(
                sprintf('Could not create ServiceInterface "%s"', $serviceInterface->getPath())
            );
        }

        Output::info(sprintf('Created ServiceInterface "%s"', $serviceInterface->getPath()));

        return $serviceInterface;
    }

    public function render(
        Component $serviceInterface,
        ?Component $repository = null,
        ?Component $entity = null,
    ): string {
        $class = new InterfaceFile($serviceInterface->getNamespace(), $serviceInterface->getClassName());
        if ($repository !== null && $entity !== null) {
            $class
                ->useClass(Import::DOCTRINE_ORM_QUERYBUILDER)
                ->useClass($repository->getFqcn())
                ->useClass($entity->getFqcn())
                ->addDeclaration(
                    (new Declaration($repository->getGetterName()))
                        ->setReturnType($repository->getClassName())
                )
                ->addDeclaration(
                    (new Declaration($entity->getDeleteMethodName()))
                        ->addParameter(
                            new Parameter($entity->getPropertyName(), $entity->getClassName())
                        )
                )
                ->addDeclaration(
                    (new Declaration($entity->getCollectionMethodName()))
                        ->setReturnType('QueryBuilder')
                        ->addParameter(
                            new Parameter('params', 'array')
                        )
                );
        }

        return $class->render();
    }
}
