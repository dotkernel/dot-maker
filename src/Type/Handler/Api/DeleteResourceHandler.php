<?php

declare(strict_types=1);

namespace Dot\Maker\Type\Handler\Api;

use Dot\Maker\Component;
use Dot\Maker\Component\Import;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Type\AbstractType;
use Dot\Maker\Type\FileInterface;
use Dot\Maker\Type\TypeEnum;

use function sprintf;
use function ucfirst;

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

            $serviceInterface = $this->fileSystem->serviceInterface($name);
            if ($serviceInterface->exists()) {
                Output::error(
                    sprintf(
                        'Handler "%s" already exists at %s',
                        $serviceInterface->getComponent()->getClassName(),
                        $serviceInterface->getPath()
                    )
                );
                continue;
            }

            $serviceInterface->ensureParentDirectoryExists();

            $content = $this->render($serviceInterface->getComponent());
            if (! $serviceInterface->create($content)) {
                Output::error(sprintf('Could not create Handler "%s"', $serviceInterface->getPath()), true);
            }
            Output::info(sprintf('Created Handler "%s"', $serviceInterface->getPath()));

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
            ->ensureParentDirectoryExists()
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
                    ->useClass($serviceInterface->getComponent()->getFqcn())
                ->getConstructor()
                    ->addPromotedProperty($serviceInterface->getComponent())
                    ->addInject($serviceInterface->getComponent()->getClassString());
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
        $body = '';
        if ($serviceInterface !== null && $entity !== null) {
            $body = <<<BDY

        \$this->{$serviceInterface->getPropertyName(true)}->delete{$entity->getClassName()}(
            \$request->getAttribute({$entity->getClassString()})
        );

BDY;
        }

        return $this->stub->render('handler/api/delete-resource.stub', [
            'CONSTRUCTOR'        => $handler->getConstructor()->render(),
            'HANDLE_METHOD_BODY' => $body,
            'HANDLER_CLASS_NAME' => $handler->getClassName(),
            'HANDLER_NAMESPACE'  => $handler->getNamespace(),
            'RESOURCE_INJECTOR'  => $handler->getAccessor()->renderResourceAttribute($entity),
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
