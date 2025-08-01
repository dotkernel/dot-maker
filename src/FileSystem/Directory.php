<?php

declare(strict_types=1);

namespace Dot\Maker\FileSystem;

use Dot\Maker\Component;

use function file_exists;
use function mkdir;
use function sprintf;

class Directory
{
    private Component $component;
    private string $name;
    private string $path;
    private string $parentDirectory;

    public function __construct(
        string $name,
        string $parentDirectory,
    ) {
        $this->name            = $name;
        $this->path            = sprintf('%s/%s', $parentDirectory, $name);
        $this->parentDirectory = $parentDirectory;
        $this->component       = new Component($name, $name);
    }

    public function create(int $permissions = 0755, bool $recursive = true): bool
    {
        return mkdir($this->path, $permissions, $recursive);
    }

    public function exists(): bool
    {
        return file_exists($this->path);
    }

    public function getComponent(): Component
    {
        return $this->component;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getParentDirectory(): string
    {
        return $this->parentDirectory;
    }
}
