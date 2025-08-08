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
            $name = ucfirst(Input::prompt('Handler name: '));
            if ($name === '') {
                break;
            }

            if (! $this->isValid($name)) {
                Output::error(sprintf('Invalid Handler name: "%s"', $name));
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
        $name = preg_replace('/Handler$/', '', $name);

        $entity = $this->fileSystem->entity($name);

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

                $template = $this->fileSystem
                    ->templateFile(
                        $entity->getComponent()->toKebabCase(),
                        sprintf('%s-list', $entity->getComponent()->toKebabCase())
                    );
                if (! $template->exists()) {
                    $template->create(sprintf('List %s template', $plural));
                    Output::success(sprintf('Created template file: %s', $template->getPath()));
                }
            }

            if (Input::confirm(sprintf('Allow viewing %s?', $plural))) {
                $this->component(TypeEnum::HandlerGetViewResource)->create($name);

                $template = $this->fileSystem
                    ->templateFile(
                        $entity->getComponent()->toKebabCase(),
                        sprintf('%s-view', $entity->getComponent()->toKebabCase())
                    );
                if (! $template->exists()) {
                    $template->create(sprintf('View %s template', $plural));
                    Output::success(sprintf('Created template file: %s', $template->getPath()));
                }
            }

            if (Input::confirm(sprintf('Allow creating %s?', $plural))) {
                $this->component(TypeEnum::HandlerGetCreateResource)->create($name);
                $this->component(TypeEnum::HandlerPostCreateResource)->create($name);
                $this->component(TypeEnum::FormCreateResource)->create($name);
                $this->component(TypeEnum::InputFilterCreateResource)->create($name);

                $template = $this->fileSystem
                    ->templateFile(
                        $entity->getComponent()->toKebabCase(),
                        sprintf('%s-create-form', $entity->getComponent()->toKebabCase())
                    );
                if (! $template->exists()) {
                    $template->create(sprintf('Create %s template', $plural));
                    Output::success(sprintf('Created template file: %s', $template->getPath()));
                }
            }

            if (Input::confirm(sprintf('Allow deleting %s?', $plural))) {
                $this->component(TypeEnum::HandlerGetDeleteResource)->create($name);
                $this->component(TypeEnum::HandlerPostDeleteResource)->create($name);
                $this->component(TypeEnum::FormDeleteResource)->create($name);
                $this->component(TypeEnum::InputFilterDeleteResource)->create($name);
                $this->component(TypeEnum::Input)->create('Confirmation');

                $template = $this->fileSystem
                    ->templateFile(
                        $entity->getComponent()->toKebabCase(),
                        sprintf('%s-delete-form', $entity->getComponent()->toKebabCase())
                    );
                if (! $template->exists()) {
                    $template->create(sprintf('Delete %s template', $plural));
                    Output::success(sprintf('Created template file: %s', $template->getPath()));
                }
            }

            if (Input::confirm(sprintf('Allow editing %s?', $plural))) {
                $this->component(TypeEnum::HandlerGetEditResource)->create($name);
                $this->component(TypeEnum::HandlerPostEditResource)->create($name);
                $this->component(TypeEnum::FormEditResource)->create($name);
                $this->component(TypeEnum::InputFilterEditResource)->create($name);

                $template = $this->fileSystem
                    ->templateFile(
                        $entity->getComponent()->toKebabCase(),
                        sprintf('%s-edit-form', $entity->getComponent()->toKebabCase())
                    );
                if (! $template->exists()) {
                    $template->create(sprintf('Edit %s template', $plural));
                    Output::success(sprintf('Created template file: %s', $template->getPath()));
                }
            }
        }

        return $this->fileSystem->handler($name);
    }
}
