<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\Import;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;

use function sprintf;
use function ucfirst;

class Service extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Service name: '));
            if ($name === '') {
                break;
            }

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid Service name: "%s"', $name));
                continue;
            }

            $service = $this->fileSystem->service($name);
            if ($service->exists()) {
                Output::error(
                    sprintf(
                        'Service "%s" already exists at %s',
                        $service->getComponent()->getClassName(),
                        $service->getPath()
                    )
                );
                continue;
            }

            $service->ensureParentDirectoryExists();

            $serviceInterface = $this->fileSystem->serviceInterface($name);

            $content = $this->render($service->getComponent(), $serviceInterface->getComponent());
            if (! $service->create($content)) {
                Output::error(sprintf('Could not create Service "%s"', $service->getPath()), true);
            }
            Output::info(sprintf('Created Service "%s"', $service->getPath()));

            $this->initComponent(TypeEnum::ServiceInterface)->create($name);

            break;
        }
    }

    public function create(string $name): File
    {
        if (! $this->isValid($name)) {
            Output::error(sprintf('Invalid Service name: "%s"', $name), true);
        }

        $service = $this->fileSystem->service($name);
        if ($service->exists()) {
            Output::error(
                sprintf(
                    'Service "%s" already exists at %s',
                    $service->getComponent()->getClassName(),
                    $service->getPath()
                ),
                true
            );
        }

        $service->ensureParentDirectoryExists();

        if ($this->module->hasRepository()) {
            $repository = $this->module->getRepository();
            $service
                ->getComponent()
                    ->useClass($repository->getComponent()->getFqcn())
                    ->useClass(Import::DOT_DEPENDENCYINJECTION_ATTRIBUTE_INJECT);
            $service
                ->getComponent()
                    ->getConstructor()
                        ->addPromotedProperty($repository->getComponent())
                        ->addInject($repository->getComponent()->getClassString());
            $service
                ->getComponent()
                    ->getAccessor()
                        ->withComponentGetter($repository->getComponent());
        }

        $serviceInterface = $this->fileSystem->serviceInterface($name);

        $content = $this->render($service->getComponent(), $serviceInterface->getComponent());
        if (! $service->create($content)) {
            Output::error(sprintf('Could not create Service "%s"', $service->getPath()), true);
        }
        Output::info(sprintf('Created Service "%s"', $service->getPath()));

        return $service;
    }

    public function render(Component $service, Component $serviceInterface): string
    {
        return $this->stub->render('service.stub', [
            'SERVICE_CLASS_NAME'     => $service->getClassName(),
            'SERVICE_NAMESPACE'      => $service->getNamespace(),
            'SERVICE_INTERFACE_NAME' => $serviceInterface->getClassName(),
            'CONSTRUCTOR'            => $service->getConstructor()->render(),
            'PROPERTY_ACCESSORS'     => $service->getAccessor()->renderGetters(),
            'USES'                   => $service->getImport()->render(),
        ]);
    }
}
