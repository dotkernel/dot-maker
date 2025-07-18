<?php

declare(strict_types=1);

namespace Dot\Maker\IO;

use function fgets;
use function sprintf;
use function strtolower;
use function trim;

use const STDIN;

class Input
{
    public static function prompt(string $prompt): string
    {
        echo $prompt;
        return trim(fgets(STDIN));
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
}
