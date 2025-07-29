<?php

declare(strict_types=1);

namespace Dot\Maker\Type\Handler;

use Dot\Maker\Component;
use Dot\Maker\Component\ClassFile;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Method\Constructor;
use Dot\Maker\Component\Parameter;
use Dot\Maker\Component\PromotedProperty;
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

class PostCreateResourceHandler extends AbstractType implements FileInterface
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

        $handler = $this->fileSystem->postResourceCreateHandler($name);
        if ($handler->exists()) {
            throw DuplicateFileException::create($handler);
        }

        $content = $this->render(
            $handler->getComponent(),
            $this->fileSystem->serviceInterface($name)->getComponent(),
            $this->fileSystem->entity($name)->getComponent(),
            $this->fileSystem->form($name)->getComponent(),
        );

        try {
            $handler->create($content);
            Output::info(sprintf('Created Handler "%s"', $handler->getPath()));
        } catch (RuntimeException $exception) {
            Output::error($exception->getMessage());
        }

        return $handler;
    }

    public function render(Component $handler, Component $serviceInterface, Component $entity, Component $form): string
    {
        $class = (new ClassFile($handler->getNamespace(), $handler->getClassName()))
            ->addInterface('RequestHandlerInterface')
            ->useClass($this->getAppMessageFqcn())
            ->useClass(Import::getConflictExceptionFqcn($this->context->getRootNamespace()))
            ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
            ->useClass(Import::DOT_FLASHMESSENGER_FLASHMESSENGERINTERFACE)
            ->useClass(Import::DOT_LOG_LOGGER)
            ->useClass(Import::FIG_HTTP_MESSAGE_STATUSCODEINTERFACE)
            ->useClass(Import::LAMINAS_DIACTOROS_RESPONSE_EMPTYRESPONSE)
            ->useClass(Import::LAMINAS_DIACTOROS_RESPONSE_HTMLRESPONSE)
            ->useClass(Import::MEZZIO_ROUTER_ROUTERINTERFACE)
            ->useClass(Import::MEZZIO_TEMPLATE_TEMPLATERENDERERINTERFACE)
            ->useClass(Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE)
            ->useClass(Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE)
            ->useClass(Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE)
            ->useClass(Import::THROWABLE)
            ->useClass($serviceInterface->getFqcn())
            ->useClass($form->getFqcn());

        $constructor = (new Constructor())
            ->addInject(
                (new Inject())
                    ->addArgument($serviceInterface->getClassString())
                    ->addArgument('RouterInterface::class')
                    ->addArgument('TemplateRendererInterface::class')
                    ->addArgument('FlashMessengerInterface::class')
                    ->addArgument($form->getClassString())
                    ->addArgument(self::wrap('dot-log.default_logger'))
            )
            ->addPromotedProperty(
                new PromotedProperty(
                    $serviceInterface->toCamelCase(true),
                    $serviceInterface->getClassName()
                )
            )
            ->addPromotedProperty(
                new PromotedProperty('router', 'RouterInterface')
            )
            ->addPromotedProperty(
                new PromotedProperty('template', 'TemplateRendererInterface')
            )
            ->addPromotedProperty(
                new PromotedProperty('messenger', 'FlashMessengerInterface')
            )
            ->addPromotedPropertyFromComponent($form)
            ->addPromotedProperty(
                new PromotedProperty('logger', 'Logger')
            );
        $class->addMethod($constructor);

        // phpcs:disable Generic.Files.LineLength.TooLong
        $handle = (new Method('handle'))
            ->setReturnType('ResponseInterface')
            ->addParameter(
                new Parameter('request', 'ServerRequestInterface')
            )
            ->setBody(<<<BODY
        \$this->{$form->toCamelCase()}
            ->setAttribute('action', \$this->router->generateUri('{$entity->toKebabCase()}::{$entity->toKebabCase()}-create'));

        try {
            \$data = (array) \$request->getParsedBody();
            \$this->{$form->toCamelCase()}->setData(\$data);
            if (\$this->{$form->toCamelCase()}->isValid()) {
                \$data = \$this->{$form->toCamelCase()}->getData();
                \$this->{$serviceInterface->toCamelCase(true)}->{$entity->getSaveMethodName()}(\$data);
                \$this->messenger->addSuccess(Message::{$entity->toUpperCase()}_CREATED);

                return new EmptyResponse(StatusCodeInterface::STATUS_CREATED);
            }

            return new HtmlResponse(
                \$this->template->render('{$entity->toKebabCase()}::{$entity->toKebabCase()}-create-form', [
                    'form' => \$this->{$form->toCamelCase()}->prepare(),
                ]),
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        } catch (ConflictException \$exception) {
            return new HtmlResponse(
                \$this->template->render('{$entity->toKebabCase()}::{$entity->toKebabCase()}-create-form', [
                    'form'     => \$this->{$form->toCamelCase()}->prepare(),
                    'messages' => [
                        'error' => \$exception->getMessage(),
                    ],
                ]),
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        } catch (Throwable \$exception) {
            \$this->logger->err('Create {$entity->getClassName()}', [
                'error' => \$exception->getMessage(),
                'file'  => \$exception->getFile(),
                'line'  => \$exception->getLine(),
                'trace' => \$exception->getTraceAsString(),
            ]);

            return new HtmlResponse(
                \$this->template->render('{$entity->toKebabCase()}::{$entity->toKebabCase()}-create-form', [
                    'form'     => \$this->{$form->toCamelCase()}->prepare(),
                    'messages' => [
                        'error' => Message::AN_ERROR_OCCURRED,
                    ],
                ]),
                StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR
            );
        }
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
