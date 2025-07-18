<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;

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

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid ServiceInterface name: "%s"', $name));
                continue;
            }

            $serviceInterface = $this->fileSystem->serviceInterface($name);
            if ($serviceInterface->exists()) {
                Output::error(
                    sprintf(
                        'ServiceInterface "%s" already exists at %s',
                        $serviceInterface->getComponent()->getClassName(),
                        $serviceInterface->getPath()
                    )
                );
                continue;
            }

            $serviceInterface->ensureParentDirectoryExists();

            $content = $this->render($serviceInterface->getComponent());
            if (! $serviceInterface->create($content)) {
                Output::error(sprintf('Could not create ServiceInterface "%s"', $serviceInterface->getPath()), true);
            }
            Output::info(sprintf('Created ServiceInterface "%s"', $serviceInterface->getPath()));

            $this->initComponent(TypeEnum::Service)->create($name);

            break;
        }
    }

    public function create(string $name): File
    {
        if (! $this->isValid($name)) {
            Output::error(sprintf('Invalid ServiceInterface name: "%s"', $name), true);
        }

        $serviceInterface = $this->fileSystem->serviceInterface($name);
        if ($serviceInterface->exists()) {
            Output::error(
                sprintf(
                    'ServiceInterface "%s" already exists at %s',
                    $serviceInterface->getComponent()->getClassName(),
                    $serviceInterface->getPath()
                ),
                true
            );
        }

        $serviceInterface->ensureParentDirectoryExists();

        $content = $this->render($serviceInterface->getComponent());
        if (! $serviceInterface->create($content)) {
            Output::error(sprintf('Could not create ServiceInterface "%s"', $serviceInterface->getPath()), true);
        }
        Output::info(sprintf('Created ServiceInterface "%s"', $serviceInterface->getPath()));

        return $serviceInterface;
    }

    public function render(Component $serviceInterface): string
    {
        return $this->stub->render('service-interface.stub', [
            'INTERFACE_NAME'      => $serviceInterface->getClassName(),
            'INTERFACE_NAMESPACE' => $serviceInterface->getNamespace(),
        ]);
    }
}
