<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use function str_repeat;

use const PHP_EOL;

class PromotedProperty extends Property
{
    public function render(int $spaces = 0): string
    {
        $spaces = str_repeat(' ', $spaces);

        $property = $spaces;
        if ($this->comment !== '') {
            $property .= $this->comment . PHP_EOL . $spaces;
        }

        $property .= $this->visibility->value;
        if ($this->static === true) {
            $property .= ' static';
        }
        if ($this->readonly === true) {
            $property .= ' readonly';
        }
        $property .= ' ' . parent::parentRender();

        return $property;
    }
}
