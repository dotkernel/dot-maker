<?php

declare(strict_types=1);

namespace Dot\Maker\Exception;

use Dot\Maker\FileSystem\File;
use RuntimeException;

use function sprintf;

class DuplicateFileException extends RuntimeException implements ExceptionInterface
{
    public static function create(File $file): self
    {
        return new self(
            sprintf('Class "%s" already exists at %s', $file->getComponent()->getClassName(), $file->getPath())
        );
    }
}
