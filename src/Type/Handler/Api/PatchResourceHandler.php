<?php

declare(strict_types=1);

namespace Dot\Maker\Type\Handler\Api;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Method\Constructor;
use Dot\Maker\Component\Parameter;
use Dot\Maker\Exception\BadRequestException;
use Dot\Maker\Exception\DuplicateFileException;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\AbstractType;
use Dot\Maker\Type\FileInterface;
use Throwable;

use function sprintf;
use function ucfirst;

class PatchResourceHandler extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Handler name: '));
            if ($name === '') {
                break;
            }

            try {
                $this->create($name);
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
            Output::error(sprintf('Invalid Handler name: "%s"', $name), true);
        }

        $handler = $this->fileSystem->apiPatchResourceHandler($name);
        if ($handler->exists()) {
            throw DuplicateFileException::create($handler);
        }

        $content = $this->render(
            $handler->getComponent(),
            $this->fileSystem->serviceInterface($name)->getComponent(),
            $this->fileSystem->inputFilter($name)->getComponent(),
            $this->fileSystem->entity($name)->getComponent(),
        );

        try {
            $handler->create($content);
            Output::info(sprintf('Created Handler "%s"', $handler->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $handler;
    }

    public function render(
        Component $handler,
        Component $serviceInterface,
        Component $inputFilter,
        Component $entity,
    ): string {
        $class = (new ClassFile($handler->getNamespace(), $handler->getClassName()))
            ->setExtends('AbstractHandler')
            ->useClass($this->import->getAbstractHandlerFqcn())
            ->useClass($this->import->getAppMessageFqcn())
            ->useClass($this->import->getBadRequestExceptionFqcn())
            ->useClass($this->import->getConflictExceptionFqcn())
            ->useClass($this->import->getNotFoundExceptionFqcn())
            ->useClass($this->import->getResourceAttributeFqcn())
            ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
            ->useClass(Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE)
            ->useClass(Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE)
            ->useClass($serviceInterface->getFqcn())
            ->useClass($inputFilter->getFqcn())
            ->useClass($entity->getFqcn());

        $constructor = (new Constructor())
            ->addPromotedPropertyFromComponent($serviceInterface)
            ->addPromotedPropertyFromComponent($inputFilter)
            ->addInject(
                (new Inject())
                    ->addArgument($serviceInterface->getClassString())
                    ->addArgument($inputFilter->getClassString())
            );
        $class->addMethod($constructor);

        // phpcs:disable Generic.Files.LineLength.TooLong
        $handle = (new Method('handle'))
            ->setReturnType('ResponseInterface')
            ->addParameter(
                new Parameter('request', 'ServerRequestInterface')
            )
            ->addInject(
                (new Inject('Resource'))->addArgument($entity->getClassString(), 'entity')
            )
            ->setComment(<<<COMM
/**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
COMM)
            ->setBody(<<<BODY
        \$this->inputFilter->setData((array) \$request->getParsedBody());
        if (! \$this->inputFilter->isValid()) {
            throw BadRequestException::create(
                detail: Message::VALIDATOR_INVALID_DATA,
                additional: ['errors' => \$this->inputFilter->getMessages()]
            );
        }

        \$data = (array) \$this->inputFilter->getValues();

        return \$this->createResponse(
            \$request,
            \$this->{$serviceInterface->toCamelCase(true)}->{$entity->getSaveMethodName()}(\$data, \$request->getAttribute({$entity->getClassString()}))
        );
BODY);
        // phpcs:enable Generic.Files.LineLength.TooLong
        $class->addMethod($handle);

        return $class->render();
    }
}
