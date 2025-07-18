<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\FileSystem\File;
use Dot\Maker\IO\Input;
use Dot\Maker\IO\Output;

use function in_array;
use function preg_match;
use function preg_replace;
use function sprintf;
use function strlen;
use function strtolower;
use function substr;
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

            break;
        }
    }

    public function create(string $name): File
    {
        $name = preg_replace('/Handler$/', '', $name);

        $plural = self::pluralize($name);
        if ($this->context->isApi()) {
//            if (Input::confirm(sprintf('Allow listing %s?', $plural))) {
//                $this->initComponent(TypeEnum::HandlerApiGetCollection)->create($name);
//            }
//            if (Input::confirm(sprintf('Allow viewing %s?', $plural))) {
//                $this->initComponent(TypeEnum::HandlerApiGetResource)->create($name);
//            }
//            if (Input::confirm(sprintf('Allow creating %s?', $plural))) {
//                $this->initComponent(TypeEnum::HandlerApiPostResource)->create($name);
//            }
            if (Input::confirm(sprintf('Allow deleting %s?', $plural))) {
                $this->initComponent(TypeEnum::HandlerApiDeleteResource)->create($name);
            }
//            if (Input::confirm(sprintf('Allow updating %s?', $plural))) {
//                $this->initComponent(TypeEnum::HandlerApiPatchResource)->create($name);
//            }
//            if (Input::confirm(sprintf('Allow replacing %s?', $plural))) {
//                $this->initComponent(TypeEnum::HandlerApiPutResource)->create($name);
//            }
//        } else {
//            if (Input::confirm(sprintf('Allow listing %s?', $plural))) {
//                $this->initComponent(TypeEnum::HandlerGetListResource)->create($name);
//            }
//            if (Input::confirm(sprintf('Allow viewing %s?', $plural))) {
//                $this->initComponent(TypeEnum::HandlerGetViewResource)->create($name);
//            }
//            if (Input::confirm(sprintf('Allow creating %s?', $plural))) {
//                $this->initComponent(TypeEnum::HandlerGetCreateResource)->create($name);
//                $this->initComponent(TypeEnum::HandlerPostCreateResource)->create($name);
//            }
//            if (Input::confirm(sprintf('Allow deleting %s?', $plural))) {
//                $this->initComponent(TypeEnum::HandlerGetDeleteResource)->create($name);
//                $this->initComponent(TypeEnum::HandlerPostDeleteResource)->create($name);
//            }
//            if (Input::confirm(sprintf('Allow updating %s?', $plural))) {
//                $this->initComponent(TypeEnum::HandlerGetEditResource)->create($name);
//                $this->initComponent(TypeEnum::HandlerPostEditResource)->create($name);
//            }
        }

        return $this->fileSystem->handler($name);
    }

    public static function pluralize(string $name): string
    {
        $lastLetter = strtolower($name[strlen($name) - 1]);

        if (in_array($lastLetter, ['s', 'x', 'z']) || preg_match('/(sh|ch)$/i', $name)) {
            return $name . 'es';
        } elseif (preg_match('/[^aeiou]y$/i', $name)) {
            return substr($name, 0, -1) . 'ies';
        } else {
            return $name . 's';
        }
    }
}
