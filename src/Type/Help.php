<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\IO\Output;

class Help extends AbstractType
{
    public function __invoke(): void
    {
        Output::info('dot-maker');
        Output::writeLine(<<<HELP

Usage: ./vendor/bin/dot-maker <component>

Where <component> must be replaced with one of the following strings:
— collection
— command
— command
— entity
— form
— handler
— input
— input-filter
— middleware
— module
— repository
— service
HELP);
    }
}
