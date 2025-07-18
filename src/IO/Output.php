<?php

declare(strict_types=1);

namespace Dot\Maker\IO;

use function fwrite;

use const PHP_EOL;
use const STDERR;
use const STDOUT;

class Output
{
    public const SUCCESS = 0;
    public const FAILURE = 1;

    public static function error(string $message, bool $exit = false): void
    {
        fwrite(STDERR, $message . PHP_EOL);
        $exit && exit(self::FAILURE);
    }

    public static function info(string $message, bool $exit = false): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
        $exit && exit(self::SUCCESS);
    }
}
