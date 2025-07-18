<?php

declare(strict_types=1);

namespace Dot\Maker;

use function file_exists;
use function file_get_contents;
use function sprintf;
use function str_replace;

class Stub
{
    private ?string $customStubDirectory;

    public function __construct(
        private readonly string $defaultStubDirectory,
        ?string $customStubDirectory = null,
    ) {
        $this->customStubDirectory = $customStubDirectory;
    }

    private function exists(string $path): bool
    {
        return file_exists($path);
    }

    private function getContents(string $path): string
    {
        return file_get_contents($path);
    }

    private function getPath(string $name): string
    {
        $path = sprintf('%s/%s', $this->customStubDirectory, $name);
        if ($this->exists($path)) {
            return $path;
        }

        return sprintf('%s/%s', $this->defaultStubDirectory, $name);
    }

    public function render(string $name, array $data): string
    {
        $contents = $this->getContents($this->getPath($name));
        foreach ($data as $key => $value) {
            $contents = str_replace(sprintf('{{%s}}', $key), $value, $contents);
        }

        return $contents;
    }
}
