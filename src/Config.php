<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\IO\Output;

use function array_key_exists;
use function file_exists;
use function sprintf;

class Config
{
    public function __construct(
        string $configPath,
    ) {
        if (! file_exists($configPath)) {
            return;
        }

        $config = require $configPath;
        if (! array_key_exists(Maker::class, $config)) {
            Output::error(sprintf('%s: key "Maker::class" not found', $configPath), true);
        }
    }
}
