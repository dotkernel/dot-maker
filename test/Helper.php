<?php

declare(strict_types=1);

namespace DotTest\Maker;

use Dot\Maker\ColorEnum;

use const PHP_EOL;

class Helper
{
    public static function getHelpText(): string
    {
        return ColorEnum::colorize('dot-maker', ColorEnum::ForegroundBrightBlue) . PHP_EOL
            . PHP_EOL
            . 'Usage:' . PHP_EOL
                . ColorEnum::colorize('./vendor/bin/dot-maker', ColorEnum::ForegroundBrightWhite)
                . ' '
                . ColorEnum::colorize('<component>', ColorEnum::ForegroundBrightYellow)
                . PHP_EOL
            . 'OR' . PHP_EOL
            . ColorEnum::colorize('composer make', ColorEnum::ForegroundBrightWhite)
                . ' '
                . ColorEnum::colorize('<component>', ColorEnum::ForegroundBrightYellow)
                . PHP_EOL
            . PHP_EOL
            . 'Where '
                . ColorEnum::colorize('<component>', ColorEnum::ForegroundBrightYellow)
                . ' must be replaced with one of the following strings:'
                . PHP_EOL
            . '— collection' . PHP_EOL
            . '— command' . PHP_EOL
            . '— entity' . PHP_EOL
            . '— form' . PHP_EOL
            . '— handler' . PHP_EOL
            . '— input' . PHP_EOL
            . '— input-filter' . PHP_EOL
            . '— middleware' . PHP_EOL
            . '— module' . PHP_EOL
            . '— repository' . PHP_EOL
            . '— service' . PHP_EOL
            . '— service-interface' . PHP_EOL;
    }
}
