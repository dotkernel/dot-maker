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

        $name = ucfirst(Input::prompt('Enter new Form name: '));
        if ($name === '') {
            return;
        }

        try {
            $this->create($name);
        } catch (Throwable $exception) {
            Output::error($exception->getMessage());
        }
    }

    public function create(string $name): File
    {
        $name = preg_replace('/Form$/', '', $name);

        if (! $this->context->isApi()) {
            $plural = Component::pluralize($name);
            if (Input::confirm(sprintf('Allow creating %s?', $plural))) {
                $this->component(TypeEnum::FormCreateResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow deleting %s?', $plural))) {
                $this->component(TypeEnum::FormDeleteResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow editing %s?', $plural))) {
                $this->component(TypeEnum::FormEditResource)->create($name);
            }
        }

        return $this->fileSystem->form($name);
    }
}
