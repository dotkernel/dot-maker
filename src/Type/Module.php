<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Throwable;

use function sprintf;
use function ucfirst;

class Module extends AbstractType implements ModuleInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new module name: '));
            if ($name === '') {
                return;
            }

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid module name: "%s"', $name));
                continue;
            }

            $module = $this->fileSystem->module($name);
            if ($module->exists()) {
                Output::error(sprintf('Module "%s" already exists at %s', $module->getName(), $module->getPath()));
                continue;
            }

            if (! $module->create()) {
                Output::error(sprintf('Could not create directory "%s"', $module->getPath()), true);
            }
            Output::success(sprintf('Created directory: "%s"', $module->getPath()));

            $this->fileSystem->setModuleName($name);

            try {
                if (Input::confirm('Create entity and repository?')) {
                    $this->component(TypeEnum::Entity)->create($module->getName());
                    $this->component(TypeEnum::Repository)->create($module->getName());
                }

                if (Input::confirm('Create service and service interface?')) {
                    $this->component(TypeEnum::Service)->create($module->getName());
                    $this->component(TypeEnum::ServiceInterface)->create($module->getName());
                }

                if (Input::confirm('Create middleware?')) {
                    $this->component(TypeEnum::Middleware)->create($module->getName());
                }

                if (Input::confirm('Create command?')) {
                    $this->component(TypeEnum::Command)->create($module->getName());
                }

                if (Input::confirm('Create handler?')) {
                    $this->component(TypeEnum::Handler)->create($module->getName());
                    $this->component(TypeEnum::RoutesDelegator)->create($module->getName());
                }

                Output::writeLine('');

                if ($this->context->isApi()) {
                    $this->component(TypeEnum::OpenApi)->create($module->getName());
                }

                $this->component(TypeEnum::ConfigProvider)->create($module->getName());

                if ($this->context->hasCore()) {
                    $this->component(TypeEnum::CoreConfigProvider)->create($module->getName());
                }
            } catch (Throwable $exception) {
                Output::error($exception->getMessage());
            }

            break;
        }
    }

    public function initExisting(): self
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter existing module name: '));
            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid module name: "%s"', $name));
                continue;
            }

            $module = $this->fileSystem->module($name);
            if (! $module->exists()) {
                Output::error(sprintf('Module "%s" not found', $name));
                continue;
            }

            $this->fileSystem->setModuleName($name);

            break;
        }

        return $this;
    }

    public function isModule(): bool
    {
        return true;
    }
}
