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

    /** @var resource $errorStream */
    private static $errorStream = STDERR;
    /** @var resource $outputStream */
    private static $outputStream = STDOUT;

    public static function error(string $message, bool $exit = false): void
    {
        $message = ColorEnum::colorize($message, ColorEnum::ForegroundBrightRed);

        fwrite(self::$errorStream, $message . PHP_EOL);
        $exit && exit(self::FAILURE);
    }

    public static function info(string $message, bool $exit = false): void
    {
        $message = ColorEnum::colorize($message, ColorEnum::ForegroundBrightBlue);

        fwrite(self::$outputStream, $message . PHP_EOL);
        $exit && exit(self::SUCCESS);
    }

    public static function success(string $message, bool $exit = false): void
    {
        $message = ColorEnum::colorize($message, ColorEnum::ForegroundBrightGreen);

        fwrite(self::$outputStream, $message . PHP_EOL);
        $exit && exit(self::SUCCESS);
    }

    public static function warning(string $message, bool $exit = false): void
    {
        $message = ColorEnum::colorize($message, ColorEnum::ForegroundBrightYellow);

        fwrite(self::$outputStream, $message . PHP_EOL);
        $exit && exit(self::SUCCESS);
    }

    public static function write(string $message = '', bool $exit = false): void
    {
        fwrite(self::$outputStream, $message);
        $exit && exit(self::SUCCESS);
    }

    public static function writeLine(string $message = '', bool $exit = false): void
    {
        fwrite(self::$outputStream, $message . PHP_EOL);
        $exit && exit(self::SUCCESS);
    }

    /**
     * @param resource $stream
     */
    public static function setErrorStream($stream): void
    {
        self::$errorStream = $stream;
    }

    /**
     * @param resource $stream
     */
    public static function setOutputStream($stream): void
    {
        self::$outputStream = $stream;
    }
}
