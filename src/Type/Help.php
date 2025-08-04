<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\IO\Output;

class Help extends AbstractType
{
    public function __invoke(): void
    {
        Output::info('WIP');
    }
}
