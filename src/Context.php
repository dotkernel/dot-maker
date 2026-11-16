<?php

declare(strict_types=1);

namespace Dot\Maker;

use JsonException;
use RuntimeException;

use function array_keys;
use function explode;
use function file_exists;
use function file_get_contents;
use function is_array;
use function json_decode;
use function sprintf;
use function str_starts_with;

use const JSON_THROW_ON_ERROR;

class Context
{
    public const NAMESPACE_ADMIN    = 'Admin';
    public const NAMESPACE_API      = 'Api';
    public const NAMESPACE_CORE     = 'Core';
    public const NAMESPACE_FRONTEND = 'Frontend';
    public const NAMESPACE_LIGHT    = 'Light';
    public const NAMESPACE_QUEUE    = 'Queue';

    private bool $hasCore = false;
    private string $projectType;
    private string $rootNamespace;

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

        $contents = file_get_contents($composerPath);
        if ($contents === false) {
            throw new RuntimeException(sprintf('%s: not readable', $composerPath));
        }

        try {
            $composer = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RuntimeException(sprintf('%s: invalid JSON', $composerPath));
        }
        if (! is_array($composer) || ! is_array($composer['autoload'] ?? null)) {
            throw new RuntimeException(sprintf('%s: key "autoload" not found', $composerPath));
        }
        if (! is_array($composer['autoload']['psr-4'] ?? null)) {
            throw new RuntimeException(sprintf('%s: key "autoload"."psr-4" not found', $composerPath));
        }

        $rootNamespace = null;
        $coreNamespace = sprintf('%s\\', self::NAMESPACE_CORE);
        foreach (array_keys($composer['autoload']['psr-4']) as $namespace) {
            $namespace = (string) $namespace;
            if (str_starts_with($namespace, $coreNamespace)) {
                $this->hasCore = true;
                continue;
            }
            if ($rootNamespace === null && $namespace !== '') {
                $rootNamespace = explode('\\', $namespace, 2)[0];
            }
        }

        if ($rootNamespace === null || $rootNamespace === '') {
            throw new RuntimeException(
                sprintf('%s: no application namespace found under "autoload"."psr-4"', $composerPath)
            );
        }

        $this->rootNamespace = $rootNamespace;
        $this->projectType   = $rootNamespace;
    }

    public function getProjectPath(): string
    {
        return $this->projectPath;
    }

    public function getProjectType(): string
    {
        return $this->projectType;
    }

    public function getRootNamespace(): string
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
