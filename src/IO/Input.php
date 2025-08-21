<?php

declare(strict_types=1);

namespace Dot\Maker\IO;

use function fgets;
use function is_resource;
use function sprintf;
use function strtolower;
use function trim;

use const PHP_EOL;
use const STDIN;

class Input
{
    /** @var resource $stream */
    private static $stream = STDIN;

    public static function prompt(string $prompt): string
    {
        Output::write(PHP_EOL . $prompt);
        return trim((string) fgets(self::$stream));
    }

    public static function confirm(string $prompt, string $default = 'yes'): bool
    {
        if ($default === 'yes') {
            $prompt = sprintf('%s [Y(es)/n(o)]: ', $prompt);
        } else {
            $prompt = sprintf('%s [y(es)/N(o)]: ', $prompt);
        }

        while (true) {
            $response = strtolower(self::prompt($prompt));
            if ($response === '') {
                $response = $default;
            }
            if ($response === 'n' || $response === 'no') {
                return false;
            }
            if ($response === 'y' || $response === 'yes') {
                return true;
            }
        }
    }

    /**
     * @return resource
     */
    public static function getStream()
    {
        if (! is_resource(self::$stream)) {
            self::$stream = STDIN;
        }

        return self::$stream;
    }

    /**
     * @param resource $stream
     */
    public static function setStream($stream): void
    {
        self::$stream = $stream;
    }
}
