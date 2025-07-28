<?php

declare(strict_types=1);

namespace Dot\Maker\Type\Handler\Api;

use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Method\Constructor;
use Dot\Maker\Component\Parameter;
use Dot\Maker\ContextInterface;
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

class PutResourceHandler extends AbstractType implements FileInterface
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

        $handler = $this->fileSystem->apiPutResourceHandler($name);
        if ($handler->exists()) {
            throw DuplicateFileException::create($handler);
        }

        $content = $this->render(
            $handler,
            $this->fileSystem->serviceInterface($name),
            $this->fileSystem->inputFilter($name),
            $this->fileSystem->entity($name),
        );

        try {
            $handler->create($content);
            Output::info(sprintf('Created Handler "%s"', $handler->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $handler;
    }

    public function render(File $handler, File $serviceInterface, File $inputFilter, File $entity): string
    {
        $class = (new ClassFile($handler->getComponent()->getNamespace(), $handler->getComponent()->getClassName()))
            ->setExtends('AbstractHandler')
            ->useClass(Import::getAbstractHandlerFqcn($this->context->getRootNamespace()))
            ->useClass(Import::getBadRequestExceptionFqcn($this->context->getRootNamespace()))
            ->useClass(Import::getConflictExceptionFqcn($this->context->getRootNamespace()))
            ->useClass(Import::getNotFoundExceptionFqcn($this->context->getRootNamespace()))
            ->useClass(Import::getResourceAttributeFqcn($this->context->getRootNamespace()))
            ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
            ->useClass(Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE)
            ->useClass(Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE)
            ->useClass($this->getAppMessageFqcn())
            ->useClass($serviceInterface->getComponent()->getFqcn())
            ->useClass($inputFilter->getComponent()->getFqcn())
            ->useClass($entity->getComponent()->getFqcn());

        $constructor = (new Constructor())
            ->addPromotedPropertyFromComponent($serviceInterface->getComponent())
            ->addPromotedPropertyFromComponent($inputFilter->getComponent())
            ->addInject(
                (new Inject())
                    ->addArgument($serviceInterface->getComponent()->getClassString())
                    ->addArgument($inputFilter->getComponent()->getClassString())
            );
        $class->addMethod($constructor);

        // phpcs:disable Generic.Files.LineLength.TooLong
        $handle = (new Method('handle'))
            ->setReturnType('ResponseInterface')
            ->addParameter(
                new Parameter('request', 'ServerRequestInterface')
            )
            ->addInject(
                (new Inject('Resource'))->addArgument($entity->getComponent()->getClassString(), 'entity')
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
            \$this->{$serviceInterface->getComponent()->getPropertyName(true)}->{$entity->getComponent()->getSaveMethodName()}(\$data, \$request->getAttribute({$entity->getComponent()->getClassString()}))
        );
BODY);
        // phpcs:enable Generic.Files.LineLength.TooLong
        $class->addMethod($handle);

        return $class->render();
    }

    public function getAppMessageFqcn(): string
    {
        $format = Import::ROOT_APP_MESSAGE;

        if ($this->context->hasCore()) {
            return sprintf($format, ContextInterface::NAMESPACE_CORE);
        }

        return sprintf($format, $this->context->getRootNamespace());
    }
}
