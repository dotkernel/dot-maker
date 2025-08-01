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

class Handler extends AbstractType implements FileInterface
{
    public function __invoke(): void
    {
        while (true) {
            $name = ucfirst(Input::prompt('Enter new Handler name: '));
            if ($name === '') {
                break;
            }

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid Handler name: "%s"', $name));
                continue;
            }

            $this->create($name);
        }
    }

    public function create(string $name): File
    {
        $name = preg_replace('/Handler$/', '', $name);

        $plural = Component::pluralize($name);
        if ($this->context->isApi()) {
            if (Input::confirm(sprintf('Allow listing %s?', $plural))) {
                $this->initComponent(TypeEnum::Collection)->create($name);
                $this->initComponent(TypeEnum::HandlerApiGetCollection)->create($name);
            }
            if (Input::confirm(sprintf('Allow viewing %s?', $plural))) {
                $this->initComponent(TypeEnum::HandlerApiGetResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow creating %s?', $plural))) {
                $this->initComponent(TypeEnum::HandlerApiPostResource)->create($name);
                $this->initComponent(TypeEnum::InputFilterCreateResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow deleting %s?', $plural))) {
                $this->initComponent(TypeEnum::HandlerApiDeleteResource)->create($name);
                $this->initComponent(TypeEnum::InputFilterDeleteResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow editing %s?', $plural))) {
                $this->initComponent(TypeEnum::HandlerApiPatchResource)->create($name);
                $this->initComponent(TypeEnum::InputFilterEditResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow replacing %s?', $plural))) {
                $this->initComponent(TypeEnum::HandlerApiPutResource)->create($name);
                $this->initComponent(TypeEnum::InputFilterReplaceResource)->create($name);
            }
        } else {
            if (Input::confirm(sprintf('Allow listing %s?', $plural))) {
                $this->initComponent(TypeEnum::HandlerGetListResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow viewing %s?', $plural))) {
                $this->initComponent(TypeEnum::HandlerGetViewResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow creating %s?', $plural))) {
                $this->initComponent(TypeEnum::HandlerGetCreateResource)->create($name);
                $this->initComponent(TypeEnum::HandlerPostCreateResource)->create($name);
                $this->initComponent(TypeEnum::FormCreateResource)->create($name);
                $this->initComponent(TypeEnum::InputFilterCreateResource)->create($name);
            }
            if (Input::confirm(sprintf('Allow deleting %s?', $plural))) {
                $this->initComponent(TypeEnum::HandlerGetDeleteResource)->create($name);
                $this->initComponent(TypeEnum::HandlerPostDeleteResource)->create($name);
                $this->initComponent(TypeEnum::FormDeleteResource)->create($name);
                $this->initComponent(TypeEnum::InputFilterDeleteResource)->create($name);
                $this->initComponent(TypeEnum::Input)->create(sprintf('%sConfirmation', $name));
            }
            if (Input::confirm(sprintf('Allow editing %s?', $plural))) {
                $this->initComponent(TypeEnum::HandlerGetEditResource)->create($name);
                $this->initComponent(TypeEnum::HandlerPostEditResource)->create($name);
                $this->initComponent(TypeEnum::FormEditResource)->create($name);
                $this->initComponent(TypeEnum::InputFilterEditResource)->create($name);
            }
        }

        return $this->fileSystem->handler($name);
    }
}
