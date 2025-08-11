<?php

declare(strict_types=1);

namespace Dot\Maker;

use RuntimeException;

use function array_key_exists;
use function explode;
use function file_exists;
use function file_get_contents;
use function json_decode;
use function sprintf;
use function str_starts_with;

class Context
{
    public const NAMESPACE_ADMIN    = 'Admin';
    public const NAMESPACE_API      = 'Api';
    public const NAMESPACE_CORE     = 'Core';
    public const NAMESPACE_FRONTEND = 'Frontend';
    public const NAMESPACE_LIGHT    = 'Light';
    public const NAMESPACE_QUEUE    = 'Queue';

    private bool $hasCore          = false;
    private ?string $projectType   = null;
    private ?string $rootNamespace = null;

    /**
     * @throws RuntimeException
     */
    public function __construct(
        private readonly string $projectPath,
    ) {
        $composerPath = sprintf('%s/composer.json', $this->projectPath);
        if (! file_exists($composerPath)) {
            throw new RuntimeException(sprintf('%s: not found', $composerPath));
        }

        $composer = json_decode(file_get_contents($composerPath), true);
        if ($composer === null) {
            throw new RuntimeException(sprintf('%s: invalid JSON', $composerPath));
        }
        if (! array_key_exists('autoload', $composer)) {
            throw new RuntimeException(sprintf('%s: key "autoload" not found', $composerPath));
        }
        if (! array_key_exists('psr-4', $composer['autoload'])) {
            throw new RuntimeException(sprintf('%s: key "autoload"."psr-4" not found', $composerPath));
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

    public function getProjectPath(): string
    {
        return $this->projectPath;
    }

    public function getProjectType(): ?string
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

    public function isAdmin(): bool
    {
        return $this->projectType === self::NAMESPACE_ADMIN;
    }

    public function isApi(): bool
    {
        return $this->projectType === self::NAMESPACE_API;
    }

    public function isFrontend(): bool
    {
        return $this->projectType === self::NAMESPACE_FRONTEND;
    }

    public function isLight(): bool
    {
        return $this->projectType === self::NAMESPACE_LIGHT;
    }

    public function isQueue(): bool
    {
        return $this->projectType === self::NAMESPACE_QUEUE;
    }
}
