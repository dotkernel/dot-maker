<?php

declare(strict_types=1);

namespace Dot\Maker\Type\Handler\Api;

use Dot\Maker\Component;
use Dot\Maker\Component\Import;
use Dot\Maker\Component\Inject;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\Method\Constructor;
use Dot\Maker\Component\Parameter;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\AbstractType;
use Dot\Maker\Type\FileInterface;
use Dot\Maker\Type\TypeEnum;

use function implode;
use function sprintf;
use function ucfirst;

use const PHP_EOL;

class DeleteResourceHandler extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Handler name: '));
            if ($name === '') {
                break;
            }

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid Handler name: "%s"', $name));
                continue;
            }

            $handler = $this->fileSystem->apiDeleteResourceHandler($name);
            if ($handler->exists()) {
                Output::error(
                    sprintf(
                        'Handler "%s" already exists at %s',
                        $handler->getComponent()->getClassName(),
                        $handler->getPath()
                    )
                );
                continue;
            }

            $content = $this->render($handler->getComponent());
            if (! $handler->create($content)) {
                Output::error(sprintf('Could not create Handler "%s"', $handler->getPath()), true);
            }
            Output::info(sprintf('Created Handler "%s"', $handler->getPath()));

            $this->initComponent(TypeEnum::Service)->create($name);

            break;
        }
    }

    public function create(string $name): File
    {
        if (! $this->isValid($name)) {
            Output::error(sprintf('Invalid Handler name: "%s"', $name), true);
        }

        $handler = $this->fileSystem->apiDeleteResourceHandler($name);
        if ($handler->exists()) {
            Output::error(
                sprintf(
                    'Handler "%s" already exists at %s',
                    $handler->getComponent()->getClassName(),
                    $handler->getPath()
                ),
                true
            );
        }

        $handler
            ->getComponent()
                ->useClass($this->getAbstractHandlerFqcn())
                ->useClass(Import::PSR_HTTP_MESSAGE_RESPONSEINTERFACE)
                ->useClass(Import::PSR_HTTP_MESSAGE_SERVERREQUESTINTERFACE);

        $entity = $this->fileSystem->entity($name);
        if ($entity->exists()) {
            $handler
                ->getComponent()
                    ->useClass($this->getResourceAttributeFqcn())
                    ->useClass($entity->getComponent()->getFqcn());
        }

        $serviceInterface = $this->fileSystem->serviceInterface($name);
        if ($serviceInterface->exists()) {
            $handler
                ->getComponent()
                    ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT)
                    ->useClass($serviceInterface->getComponent()->getFqcn());
        }

        $content = $this->render(
            $handler->getComponent(),
            $serviceInterface->exists() ? $serviceInterface->getComponent() : null,
            $entity->exists() ? $entity->getComponent() : null
        );
        if (! $handler->create($content)) {
            Output::error(sprintf('Could not create Handler "%s"', $handler->getPath()), true);
        }
        Output::info(sprintf('Created Handler "%s"', $handler->getPath()));

        return $handler;
    }

    public function render(Component $handler, ?Component $serviceInterface = null, ?Component $entity = null): string
    {
        $methods = [];

        if ($serviceInterface !== null) {
            $methods[] = (new Constructor())
                ->addPromotedPropertyFromComponent($serviceInterface)
                ->addInject(
                    (new Inject())->addArgument($serviceInterface->getClassString())
                );
        }

        $handle = (new Method('handle'))
            ->addParameter(
                new Parameter('request', 'ServerRequestInterface')
            )
            ->setReturnType('ResponseInterface');
        if ($entity !== null) {
            $handle
                ->addInject(
                    (new Inject('Resource'))->addArgument($entity->getClassString(), 'entity')
                )
                ->addBodyLine(
                    sprintf('$this->%s->delete%s(', $serviceInterface->getPropertyName(true), $entity->getClassName())
                )
                ->addBodyLine(sprintf('$request->getAttribute(%s)', $entity->getClassString()), 12)
                ->addBodyLine(');')
                ->addBodyLine('', 0);
        }
        $handle->addBodyLine('return $this->noContentResponse();');
        $methods[] = $handle;

        return $this->stub->render('api-handler.stub', [
            'HANDLER_CLASS_NAME' => $handler->getClassName(),
            'HANDLER_NAMESPACE'  => $handler->getNamespace(),
            'METHODS'            => implode(PHP_EOL . PHP_EOL . '    ', $methods),
            'USES'               => $handler->getImport()->render(),
        ]);
    }

    public function getAbstractHandlerFqcn(): string
    {
        return sprintf(Import::ROOT_APP_HANDLER_ABSTRACTHANDLER, $this->context->getRootNamespace());
    }

    public function getResourceAttributeFqcn(): string
    {
        return sprintf(Import::ROOT_APP_ATTRIBUTE_RESOURCE, $this->context->getRootNamespace());
    }
}
