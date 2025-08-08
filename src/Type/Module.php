<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Dot\Maker\Message;
use Throwable;

use function count;
use function ksort;
use function sprintf;
use function ucfirst;

class Module extends AbstractType implements ModuleInterface
{
    protected array $messages = [];

    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('New module name: '));
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
            Output::success(sprintf('Created directory: %s', $module->getPath()));

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

                if (Input::confirm('Create command?')) {
                    $this->component(TypeEnum::Command)->create($module->getName());
                }

                if (Input::confirm('Create middleware?')) {
                    $this->component(TypeEnum::Middleware)->create($module->getName());
                }

                if (Input::confirm('Create handler?')) {
                    $this->component(TypeEnum::Handler)->create($module->getName());
                    Output::writeLine();
                    $this->component(TypeEnum::RoutesDelegator)->create($module->getName());
                }

                Output::writeLine();

                if ($this->context->isApi()) {
                    $this->component(TypeEnum::OpenApi)->create($module->getName());
                }

                $this->component(TypeEnum::ConfigProvider)->create($module->getName());

                if ($this->context->hasCore()) {
                    $this->component(TypeEnum::CoreConfigProvider)->create($module->getName());
                }

                $this
                    ->addMessage(Message::dumpComposerAutoloader())
                    ->addMessage(new Message('Start adding logic to the new module files.'));

                $this->renderMessages();
            } catch (Throwable $exception) {
                Output::error($exception->getMessage());
            }

            break;
        }
    }

    public function addMessage(Message $message): static
    {
        $this->messages[$message->getPriority()] = (string) $message;

        return $this;
    }

    public function initExisting(): self
    {
        while (true) {
            $name = ucfirst(Input::prompt('Existing module name: '));
            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid module name: "%s"', $name));
                continue;
            }

            $module = $this->fileSystem->module($name);
            if (! $module->exists()) {
                Output::error(sprintf('Module "%s" not found', $name));
                continue;
            }

            Output::success(sprintf('Found Module "%s"', $name));

            $this->fileSystem->setModuleName($name);

            break;
        }

        return $this;
    }

    public function isModule(): bool
    {
        return true;
    }

    public function renderMessages(): void
    {
        if (count($this->messages) === 0) {
            return;
        }

        Output::writeLine();
        Output::warning('Next steps:');
        Output::warning('===========');

        ksort($this->messages);
        foreach ($this->messages as $message) {
            Output::writeLine(sprintf('- %s', $message));
        }
    }
}
