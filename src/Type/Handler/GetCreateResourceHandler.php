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
use Dot\Maker\IO\Output;
use Dot\Maker\Type\AbstractType;
use Dot\Maker\Type\FileInterface;

use function sprintf;

class GetCreateResourceHandler extends AbstractType implements FileInterface
{
    /**
     * @throws BadRequestException
     * @throws DuplicateFileException
     * @throws RuntimeException
     */
    public function create(string $name): File
    {
        $handler = $this->fileSystem->getCreateResourceHandler($name);
        if ($handler->exists()) {
            throw DuplicateFileException::create($handler);
        }

        $content = $this->render(
            $handler->getComponent(),
            $this->fileSystem->entity($name)->getComponent(),
            $this->fileSystem->createResourceForm($name)->getComponent(),
        );

        $handler->create($content);

        Output::success(sprintf('Created Handler: %s', $handler->getPath()));

        return $handler;
    }

    public function render(Component $handler, Component $entity, Component $form): string
    {
        $class = (new ClassFile($handler->getNamespace(), $handler->getClassName()))
            ->addInterface('RequestHandlerInterface')
            ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
            ->useClass(Import::LAMINAS_DIACTOROS_RESPONSE_HTMLRESPONSE)
            ->useClass(Import::MEZZIO_ROUTER_ROUTERINTERFACE)
            ->useClass(Import::MEZZIO_TEMPLATE_TEMPLATERENDERERINTERFACE)
            ->useClass(Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE)
            ->useClass(Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE)
            ->useClass(Import::PSR_HTTP_SERVER_REQUESTHANDLERINTERFACE)
            ->useClass($form->getFqcn());

        $constructor = (new Constructor())
            ->addInject(
                (new Inject())
                    ->addArgument('RouterInterface::class')
                    ->addArgument('TemplateRendererInterface::class')
                    ->addArgument($form->getClassString())
            )
            ->addPromotedProperty(
                new PromotedProperty('router', 'RouterInterface')
            )
            ->addPromotedProperty(
                new PromotedProperty('template', 'TemplateRendererInterface')
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
        \$this->{$form->toCamelCase()}
            ->setAttribute('action', \$this->router->generateUri('{$entity->toKebabCase()}::{$entity->toKebabCase()}-create'));

        return new HtmlResponse(
            \$this->template->render('{$entity->toKebabCase()}::create-{$entity->toKebabCase()}-form', [
                'form' => \$this->{$form->toCamelCase()}->prepare(),
            ])
        );
BODY);
        // phpcs:enable Generic.Files.LineLength.TooLong
        $class->addMethod($handle);

        return $class->render();
    }
}
