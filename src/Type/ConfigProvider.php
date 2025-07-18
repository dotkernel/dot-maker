<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\FileSystem\File;

class ConfigProvider extends AbstractType implements FileInterface
{
    public function __invoke(): self
    {
        dd(__FILE__);
    }

    public function getNamespace(): string
    {
        return '';
    }

    public function create(string $name): ?File
    {
        // TODO: Implement create() method.
    }
}
