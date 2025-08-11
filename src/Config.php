<?php

declare(strict_types=1);

namespace Dot\Maker;

use RuntimeException;

use function array_key_exists;
use function file_exists;
use function sprintf;

class Config
{
    public const CONFIG_FILE = 'config/autoload/maker.local.php';

    /**
     * @throws RuntimeException
     */
    public function __construct(
        private readonly string $projectPath,
    ) {
        $configPath = $this->getConfigPath();
        if (! file_exists($configPath)) {
            return;
        }

        $config = require $configPath;
        if (! array_key_exists(Maker::class, $config)) {
            throw new RuntimeException(sprintf('%s: key "Maker::class" not found', $configPath));
        }
    }

    public function getConfigPath(): string
    {
        return sprintf('%s/%s', $this->projectPath, self::CONFIG_FILE);
    }
}
