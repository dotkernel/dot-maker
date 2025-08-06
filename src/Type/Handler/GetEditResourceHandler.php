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

class GetEditResourceHandler extends AbstractType implements FileInterface
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

        $handler = $this->fileSystem->getEditResourceHandler($name);
        if ($handler->exists()) {
            throw DuplicateFileException::create($handler);
        }

        $content = $this->render(
            $handler->getComponent(),
            $this->fileSystem->entity($name)->getComponent(),
            $this->fileSystem->editResourceForm($name)->getComponent(),
            $this->fileSystem->serviceInterface($name)->getComponent(),
        );

        $handler->create($content);

        Output::success(sprintf('Created Handler: %s', $handler->getPath()));

        return $handler;
    }

    public function render(Component $handler, Component $entity, Component $form, Component $serviceInterface): string
    {
        $class = (new ClassFile($handler->getNamespace(), $handler->getClassName()))
            ->addInterface('RequestHandlerInterface')
            ->useClass($this->import->getNotFoundExceptionFqcn())
            ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
            ->useClass(Import::DOT_FLASHMESSENGER_FLASHMESSENGERINTERFACE)
            ->useClass(Import::FIG_HTTP_MESSAGE_STATUSCODEINTERFACE)
            ->useClass(Import::LAMINAS_DIACTOROS_RESPONSE_EMPTYRESPONSE)
            ->useClass(Import::LAMINAS_DIACTOROS_RESPONSE_HTMLRESPONSE)
            ->useClass(Import::MEZZIO_ROUTER_ROUTERINTERFACE)
            ->useClass(Import::MEZZIO_TEMPLATE_TEMPLATERENDERERINTERFACE)
            ->useClass(Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE)
            ->useClass(Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE)
            ->useClass(Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE)
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
            ->addPromotedPropertyFromComponent($form);
        $class->addMethod($constructor);

        // phpcs:disable Generic.Files.LineLength.TooLong
        $handle = (new Method('handle'))
            ->setReturnType('ResponseInterface')
            ->addParameter(
                new Parameter('request', 'ServerRequestInterface')
            )
            ->setBody(<<<BODY
        try {
            {$entity->getVariable()} = \$this->{$serviceInterface->toCamelCase(true)}->{$entity->getFindMethodName()}(\$request->getAttribute('uuid'));
        } catch (NotFoundException \$exception) {
            \$this->messenger->addError(\$exception->getMessage());

            return new EmptyResponse(StatusCodeInterface::STATUS_NOT_FOUND);
        }

        \$this->{$form->toCamelCase()}
            ->setAttribute(
                'action',
                \$this->router->generateUri('{$entity->toKebabCase()}::{$entity->toKebabCase()}-edit', ['uuid' => {$entity->getVariable()}->getUuid()->toString()])
            )
            ->bind({$entity->getVariable()});

        return new HtmlResponse(
            \$this->template->render('{$entity->toKebabCase()}::{$entity->toKebabCase()}-edit-form', [
                'form' => \$this->{$form->toCamelCase()}->prepare(),
                '{$entity->toKebabCase()}' => {$entity->getVariable()},
            ])
        );
BODY);
        // phpcs:enable Generic.Files.LineLength.TooLong
        $class->addMethod($handle);

        return $class->render();
    }
}
