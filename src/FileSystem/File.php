<?php

declare(strict_types=1);

namespace Dot\Maker\FileSystem;

use Dot\Maker\Component;
use Dot\Maker\Exception\RuntimeException;

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

    /**
     * @throws RuntimeException
     */
    public function create(string $data): void
    {
        $this->ensureParentDirectoryExists();

        $created = file_put_contents($this->path, $data);

        if ($created === false) {
            throw new RuntimeException(
                sprintf('Could not create file "%s"', $this->path)
            );
        }
    }

    /**
     * @throws RuntimeException
     */
    public function ensureParentDirectoryExists(): self
    {
        if (! $this->parentDirectory->exists()) {
            if (! $this->parentDirectory->create()) {
                throw new RuntimeException(
                    sprintf('Could not create parent directory "%s"', $this->parentDirectory->getPath())
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
