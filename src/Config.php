<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\IO\Output;

use function array_key_exists;
use function file_exists;
use function sprintf;

class Config
{
    private ?string $customStubDirectory = null;

    public function __construct(
        private string $defaultStubDirectory,
        string $configPath,
    ) {
        if (! file_exists($configPath)) {
            return;
        }

        $config = require $configPath;
        if (! array_key_exists(Maker::class, $config)) {
            Output::error(sprintf('%s: key "Maker::class" not found', $configPath), true);
        }

        if (array_key_exists('stub_directory', $config[Maker::class])) {
            $this->customStubDirectory = $config[Maker::class]['stub_directory'];
        }
    }

    public function getCustomStubDirectory(): ?string
    {
        return $this->customStubDirectory;
    }

    public function getDefaultStubDirectory(): string
    {
        return $this->defaultStubDirectory;
    }
}
