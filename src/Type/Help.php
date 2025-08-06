<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\ColorEnum;
use Dot\Maker\IO\Output;
use Dot\Maker\Message;

class Help extends AbstractType
{
    public function __invoke(): void
    {
        Output::info('dot-maker');
        Output::writeLine();
        Output::writeLine(
            (string) (new Message('Usage: '))
                ->appendLine(
                    ColorEnum::colorize('./vendor/bin/dot-maker', ColorEnum::ForegroundBrightWhite)
                )
                ->append(' ')
                ->append(
                    ColorEnum::colorize('<component>', ColorEnum::ForegroundBrightYellow)
                )
        );
        Output::writeLine('OR');
        Output::writeLine(
            (string) (new Message())
                ->append(
                    ColorEnum::colorize('composer make', ColorEnum::ForegroundBrightWhite)
                )
                ->append(' ')
                ->append(
                    ColorEnum::colorize('<component>', ColorEnum::ForegroundBrightYellow)
                )
        );
        Output::writeLine();
        Output::writeLine(
            (string) (new Message('Where '))
                ->append(
                    ColorEnum::colorize('<component>', ColorEnum::ForegroundBrightYellow)
                )
                ->append(' must be replaced with one of the following strings:')
        );
        Output::writeLine('— collection');
        Output::writeLine('— command');
        Output::writeLine('— entity');
        Output::writeLine('— form');
        Output::writeLine('— handler');
        Output::writeLine('— input');
        Output::writeLine('— input-filter');
        Output::writeLine('— middleware');
        Output::writeLine('— module');
        Output::writeLine('— repository');
        Output::writeLine('— service');
        Output::writeLine('— service-interface');
    }
}
