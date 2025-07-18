<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\FileSystem\File;

interface FileInterface
{
    public function create(string $name): ?File;
}
