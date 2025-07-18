<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\IO\Output;

use function array_key_exists;
use function explode;
use function file_exists;
use function file_get_contents;
use function json_decode;
use function sprintf;
use function str_starts_with;

class Context implements ContextInterface
{
    private bool $hasCore          = false;
    private ?string $projectType   = null;
    private ?string $rootNamespace = null;

    public function __construct(string $composerPath)
    {
        if (! file_exists($composerPath)) {
            Output::error(sprintf('composer.json not found at "%s"', $composerPath), true);
        }

        $composer = json_decode(file_get_contents($composerPath), true);
        if (! array_key_exists('autoload', $composer)) {
            Output::error('composer.json: key "autoload" not found', true);
        }
        if (! array_key_exists('psr-4', $composer['autoload'])) {
            Output::error('composer.json: key "psr-4" not found in "autoload"', true);
        }

        $coreNamespace = sprintf('%s\\', self::NAMESPACE_CORE);
        foreach ($composer['autoload']['psr-4'] as $namespace => $modulePath) {
            if (str_starts_with($namespace, $coreNamespace)) {
                $this->hasCore = true;
                continue;
            }
            if ($this->rootNamespace === null) {
                $namespace           = explode('\\', $namespace, 2);
                $this->rootNamespace = $namespace[0];
                $this->projectType   = $namespace[0];
            }
        }
    }

    public function getProjectType(): string
    {
        return $this->projectType;
    }

    public function getRootNamespace(): ?string
    {
        return $this->rootNamespace;
    }

    public function hasCore(): bool
    {
        return $this->hasCore;
    }

    public function isApi(): bool
    {
        return $this->projectType === self::NAMESPACE_API;
    }
}
