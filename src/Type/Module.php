<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;

use function count;
use function sprintf;
use function ucfirst;

class Module extends AbstractType implements ModuleInterface
{
    private ?File $collection       = null;
    private ?File $command          = null;
    private ?File $entity           = null;
    private ?File $form             = null;
    private ?File $inputFilter      = null;
    private ?File $middleware       = null;
    private ?File $repository       = null;
    private ?File $service          = null;
    private ?File $serviceInterface = null;
    private array $inputs           = [];

    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new module name: '));
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
            Output::info(sprintf('Created directory: "%s"', $module->getPath()));

            $this->fileSystem->setModuleName($name);

            if (Input::confirm('Create entity?')) {
                $this->entity = $this->initComponent(TypeEnum::Entity)->create($module->getName());
                if ($this->hasEntity()) {
                    $this->repository = $this->initComponent(TypeEnum::Repository)->create($module->getName());
                }
            }

            if (Input::confirm('Create service?')) {
                $this->service = $this->initComponent(TypeEnum::Service)->create($module->getName());
                if ($this->hasService()) {
                    $this->serviceInterface =
                        $this->initComponent(TypeEnum::ServiceInterface)->create($module->getName());
                }
            }

            if (Input::confirm('Create middleware?')) {
                $this->middleware = $this->initComponent(TypeEnum::Middleware)->create($module->getName());
            }

            if (Input::confirm('Create command?')) {
                $this->command = $this->initComponent(TypeEnum::Command)->create($module->getName());
            }

            if (Input::confirm('Create handler?')) {
                $this->initComponent(TypeEnum::Handler)->create($module->getName());
            }

            if ($this->context->isApi()) {
                $this->initComponent(TypeEnum::OpenApi)->create($module->getName());
            } else {
                $templates = $this->fileSystem->templates();
                if (! $templates->exists()) {
                    $templates->create();
                }
                $templatesDir = $this->fileSystem->templatesDir($this->entity->getComponent()->toKebabCase());
                if (! $templatesDir->exists()) {
                    $templatesDir->create();
                }
            }

            $this->initComponent(TypeEnum::RoutesDelegator)->create($module->getName());
            $this->initComponent(TypeEnum::ConfigProvider)->create($module->getName());

            if ($this->context->hasCore()) {
                $this->initComponent(TypeEnum::CoreConfigProvider)->create($module->getName());
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

    public function getCollection(): ?File
    {
        return $this->collection;
    }

    public function hasCollection(): bool
    {
        return $this->collection !== null;
    }

    public function getCommand(): ?File
    {
        return $this->command;
    }

    public function hasCommand(): bool
    {
        return $this->command !== null;
    }

    public function getEntity(): ?File
    {
        return $this->entity;
    }

    public function hasEntity(): bool
    {
        return $this->entity !== null;
    }

    public function getForm(): ?File
    {
        return $this->form;
    }

    public function hasForm(): bool
    {
        return $this->form !== null;
    }

    public function getInputs(): array
    {
        return $this->inputs;
    }

    public function hasInputs(): bool
    {
        return count($this->inputs) > 0;
    }

    public function getInputFilter(): ?File
    {
        return $this->inputFilter;
    }

    public function hasInputFilter(): bool
    {
        return $this->inputFilter !== null;
    }

    public function getMiddleware(): ?File
    {
        return $this->middleware;
    }

    public function hasMiddleware(): bool
    {
        return $this->middleware !== null;
    }

    public function getRepository(): ?File
    {
        return $this->repository;
    }

    public function hasRepository(): bool
    {
        return $this->repository !== null;
    }

    public function getService(): ?File
    {
        return $this->service;
    }

    public function hasService(): bool
    {
        return $this->service !== null;
    }

    public function getServiceInterface(): ?File
    {
        return $this->serviceInterface;
    }

    public function hasServiceInterface(): bool
    {
        return $this->serviceInterface !== null;
    }

    public function isModule(): bool
    {
        return true;
    }
}
