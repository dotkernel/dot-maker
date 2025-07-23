<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\VisibilityEnum;

use function sprintf;

class Property extends Parameter
{
    protected VisibilityEnum $visibility = VisibilityEnum::Protected;
    protected bool $isPromoted           = false;

    public function render(): string
    {
        $property = sprintf('%s %s', $this->visibility->value, parent::render());

        if ($this->isPromoted) {
            return $property . ',';
        }

        return $property . ';';
    }

    public function setVisibility(VisibilityEnum $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }
}
