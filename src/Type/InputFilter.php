<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;

use function preg_replace;
use function sprintf;
use function ucfirst;

class InputFilter extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new InputFilter name: '));
            if ($name === '') {
                break;
            }

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid InputFilter name: "%s"', $name));
                continue;
            }

            $this->create($name);

            break;
        }
    }

    public function create(string $name): File
    {
        $name = preg_replace('/Form$/', '', $name);

        $plural = Component::pluralize($name);
        if (Input::confirm(sprintf('Allow creating %s?', $plural))) {
            $this->initComponent(TypeEnum::InputFilterCreateResource)->create($name);
        }
        if (Input::confirm(sprintf('Allow deleting %s?', $plural))) {
            $this->initComponent(TypeEnum::InputFilterDeleteResource)->create($name);
        }
        if (Input::confirm(sprintf('Allow editing %s?', $plural))) {
            $this->initComponent(TypeEnum::InputFilterEditResource)->create($name);
        }
        if ($this->context->isApi()) {
            if (Input::confirm(sprintf('Allow replacing %s?', $plural))) {
                $this->initComponent(TypeEnum::InputFilterReplaceResource)->create($name);
            }
        }

        return $this->fileSystem->form($name);
    }
}
