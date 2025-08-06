<?php

declare(strict_types=1);

namespace Dot\Maker\IO;

use Dot\Maker\ColorEnum;

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
        $message = ColorEnum::colorize($message, ColorEnum::ForegroundBrightRed);

        fwrite(STDERR, $message . PHP_EOL);
        $exit && exit(self::FAILURE);
    }

    public static function info(string $message, bool $exit = false): void
    {
        $message = ColorEnum::colorize($message, ColorEnum::ForegroundBrightBlue);

        fwrite(STDOUT, $message . PHP_EOL);
        $exit && exit(self::SUCCESS);
    }

    public static function success(string $message, bool $exit = false): void
    {
        $message = ColorEnum::colorize($message, ColorEnum::ForegroundBrightGreen);

        fwrite(STDOUT, $message . PHP_EOL);
        $exit && exit(self::SUCCESS);
    }

    public static function warning(string $message, bool $exit = false): void
    {
        $message = ColorEnum::colorize($message, ColorEnum::ForegroundBrightYellow);

        fwrite(STDOUT, $message . PHP_EOL);
        $exit && exit(self::SUCCESS);
    }

    public static function write(string $message = '', bool $exit = false): void
    {
        fwrite(STDOUT, $message);
        $exit && exit(self::SUCCESS);
    }

    public static function writeLine(string $message = '', bool $exit = false): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
        $exit && exit(self::SUCCESS);
    }
}
