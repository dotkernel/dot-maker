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

class InputFilter extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('InputFilter name: '));
            if ($name === '') {
                return;
            }

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid InputFilter name: "%s"', $name));
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
        $name = preg_replace('/InputFilter$/', '', $name);

        $plural = Component::pluralize($name);
        if (Input::confirm(sprintf('Allow creating %s?', $plural))) {
            $this->component(TypeEnum::InputFilterCreateResource)->create($name);
        }
        if (Input::confirm(sprintf('Allow deleting %s?', $plural))) {
            $this->component(TypeEnum::InputFilterDeleteResource)->create($name);
        }
        if (Input::confirm(sprintf('Allow editing %s?', $plural))) {
            $this->component(TypeEnum::InputFilterEditResource)->create($name);
        }
        if ($this->context->isApi()) {
            if (Input::confirm(sprintf('Allow replacing %s?', $plural))) {
                $this->component(TypeEnum::InputFilterReplaceResource)->create($name);
            }
        }

        return $this->fileSystem->form($name);
    }
}
