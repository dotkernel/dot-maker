<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;
use Throwable;

use function preg_replace;
use function sprintf;
use function ucfirst;

class Form extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        if ($this->context->isApi()) {
            Output::error('Cannot create Forms in an API');
            return;
        }

        while (true) {
            $name = ucfirst(Input::prompt('Form name: '));
            if ($name === '') {
                return;
            }

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid Form name: "%s"', $name));
                continue;
            }

            try {
                $this->create($name);
            } catch (Throwable $exception) {
                Output::error($exception->getMessage());
            }
        }
    }

    public function create(string $name): File
    {
        $name = preg_replace('/Form$/', '', $name);

        if (! $this->context->isApi()) {
            $plural = Component::pluralize($name);
            if (Input::confirm(sprintf('Allow creating %s?', $plural))) {
                $this->component(TypeEnum::FormCreateResource)->create($name);
                $this->component(TypeEnum::InputFilterCreateResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow deleting %s?', $plural))) {
                $this->component(TypeEnum::FormDeleteResource)->create($name);
                $this->component(TypeEnum::InputFilterDeleteResource)->create($name);
                $this->component(TypeEnum::Input)->create(sprintf('%sConfirmation', $name));
            }
            if (Input::confirm(sprintf('Allow editing %s?', $plural))) {
                $this->component(TypeEnum::FormEditResource)->create($name);
                $this->component(TypeEnum::InputFilterEditResource)->create($name);
            }
        }

        return $this->fileSystem->form($name);
    }
}
