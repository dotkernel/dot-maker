<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\Component;
use Dot\Maker\VisibilityEnum;

use function sprintf;

readonly class Property
{
    public function __construct(
        private Component $component,
        private VisibilityEnum $visibility = VisibilityEnum::Protected,
        private bool $nullable = false,
        private mixed $default = null,
        private bool $isPromoted = false,
    ) {
    }

    public function render(): string
    {
        $property = sprintf('%s %s', $this->component->getClassName(), $this->component->getVariable());
        if ($this->nullable) {
            $property = sprintf('?%s', $property);
        }
        if ($this->default !== null) {
            $property = sprintf('%s = %s', $property, $this->default);
        }
        $property = sprintf('%s %s', $this->visibility->value, $property);

        if ($this->isPromoted) {
            return $property . ',';
        }

        return $property . ';';
    }
}
