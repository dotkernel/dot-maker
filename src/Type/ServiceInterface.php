<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

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
            $serviceInterface,
            $this->fileSystem->repository($name),
            $this->fileSystem->entity($name),
        );

        try {
            $serviceInterface->create($content);
            Output::info(sprintf('Created ServiceInterface "%s"', $serviceInterface->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $serviceInterface;
    }

    public function render(File $serviceInterface, File $repository, File $entity): string
    {
        $class = (new InterfaceFile(
            $serviceInterface->getComponent()->getNamespace(),
            $serviceInterface->getComponent()->getClassName()
        ))
            ->useClass(Import::DOCTRINE_ORM_QUERYBUILDER)
            ->useClass($repository->getComponent()->getFqcn())
            ->useClass($entity->getComponent()->getFqcn())
            ->addDeclaration(
                (new Declaration($repository->getComponent()->getGetterName()))
                    ->setReturnType($repository->getComponent()->getClassName())
            )
            ->addDeclaration(
                (new Declaration($entity->getComponent()->getDeleteMethodName()))
                    ->addParameter(
                        new Parameter($entity->getComponent()->getPropertyName(), $entity->getComponent()->getClassName())
                    )
            )
            ->addDeclaration(
                (new Declaration($entity->getComponent()->getCollectionMethodName()))
                    ->setReturnType('QueryBuilder')
                    ->addParameter(
                        new Parameter('params', 'array')
                    )
            )
            ->addDeclaration(
                (new Declaration($entity->getComponent()->getSaveMethodName()))
                    ->setReturnType($entity->getComponent()->getClassName())
                    ->addParameter(
                        new Parameter('data', 'array')
                    )
                    ->addParameter(
                        new Parameter(
                            $entity->getComponent()->getPropertyName(),
                            $entity->getComponent()->getClassName(),
                            true,
                            'null'
                        )
                    )
            );

        return $class->render();
    }
}
