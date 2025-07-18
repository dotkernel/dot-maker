<?php

declare(strict_types=1);

namespace Dot\Maker\FileSystem;

use Dot\Maker\Component;
use Dot\Maker\IO\Output;

use function file_exists;
use function file_put_contents;
use function sprintf;

class File
{
    private Component $component;
    private string $name;
    private string $path;

    public function __construct(
        private readonly Directory $parentDirectory,
        string $namespace,
        string $className,
    ) {
        $this->name      = sprintf('%s.php', $className);
        $this->path      = sprintf('%s/%s', $parentDirectory->getPath(), $this->name);
        $this->component = new Component($namespace, $className);
    }

    public function create(string $data): bool
    {
        return (bool) file_put_contents($this->path, $data);
    }

    public function ensureParentDirectoryExists(bool $exit = true): self
    {
        if (! $this->parentDirectory->exists()) {
            if (! $this->parentDirectory->create()) {
                Output::error(
                    sprintf('Could not create directory "%s"', $this->parentDirectory->getPath()),
                    $exit
                );
            }
        }

        return $this;
    }

    public function exists(): bool
    {
        return file_exists($this->path);
    }

    public function getParentDirectory(): Directory
    {
        return $this->parentDirectory;
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
}
