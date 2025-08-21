<?php

declare(strict_types=1);

namespace Dot\Maker\IO;

use Dot\Maker\ColorEnum;

use function fwrite;
use function is_resource;

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
     * @return resource
     */
    public static function getErrorStream()
    {
        if (! is_resource(self::$errorStream)) {
            self::$errorStream = STDERR;
        }

        return self::$errorStream;
    }

    /**
     * @param resource $stream
     */
    public static function setErrorStream($stream): void
    {
        self::$errorStream = $stream;
    }

    /**
     * @return resource
     */
    public static function getOutputStream()
    {
        if (! is_resource(self::$outputStream)) {
            self::$outputStream = STDOUT;
        }

        return self::$outputStream;
    }

    /**
     * @param resource $stream
     */
    public static function setOutputStream($stream): void
    {
        self::$outputStream = $stream;
    }
}
