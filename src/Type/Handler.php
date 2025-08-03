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

class Handler extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Handler name: '));
            if ($name === '') {
                break;
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
        $name = preg_replace('/Handler$/', '', $name);

        $plural = Component::pluralize($name);
        if ($this->context->isApi()) {
            if (Input::confirm(sprintf('Allow listing %s?', $plural))) {
                $this->component(TypeEnum::Collection)->create($name);
                $this->component(TypeEnum::HandlerApiGetCollection)->create($name);
            }
            if (Input::confirm(sprintf('Allow viewing %s?', $plural))) {
                $this->component(TypeEnum::HandlerApiGetResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow creating %s?', $plural))) {
                $this->component(TypeEnum::HandlerApiPostResource)->create($name);
                $this->component(TypeEnum::InputFilterCreateResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow deleting %s?', $plural))) {
                $this->component(TypeEnum::HandlerApiDeleteResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow editing %s?', $plural))) {
                $this->component(TypeEnum::HandlerApiPatchResource)->create($name);
                $this->component(TypeEnum::InputFilterEditResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow replacing %s?', $plural))) {
                $this->component(TypeEnum::HandlerApiPutResource)->create($name);
                $this->component(TypeEnum::InputFilterReplaceResource)->create($name);
            }
        } else {
            if (Input::confirm(sprintf('Allow listing %s?', $plural))) {
                $this->component(TypeEnum::HandlerGetListResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow viewing %s?', $plural))) {
                $this->component(TypeEnum::HandlerGetViewResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow creating %s?', $plural))) {
                $this->component(TypeEnum::HandlerGetCreateResource)->create($name);
                $this->component(TypeEnum::HandlerPostCreateResource)->create($name);
                $this->component(TypeEnum::FormCreateResource)->create($name);
                $this->component(TypeEnum::InputFilterCreateResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow deleting %s?', $plural))) {
                $this->component(TypeEnum::HandlerGetDeleteResource)->create($name);
                $this->component(TypeEnum::HandlerPostDeleteResource)->create($name);
                $this->component(TypeEnum::FormDeleteResource)->create($name);
                $this->component(TypeEnum::InputFilterDeleteResource)->create($name);
                $this->component(TypeEnum::Input)->create(sprintf('%sConfirmation', $name));
            }
            if (Input::confirm(sprintf('Allow editing %s?', $plural))) {
                $this->component(TypeEnum::HandlerGetEditResource)->create($name);
                $this->component(TypeEnum::HandlerPostEditResource)->create($name);
                $this->component(TypeEnum::FormEditResource)->create($name);
                $this->component(TypeEnum::InputFilterEditResource)->create($name);
            }
        }

        return $this->fileSystem->handler($name);
    }
}
