<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\Component;
use Dot\Maker\VisibilityEnum;

use function array_merge;
use function array_values;
use function implode;
use function sprintf;

use const PHP_EOL;

class Accessor
{
    private array $getters = [];
    private array $setters = [];

    public function render(): string
    {
        return PHP_EOL . implode(PHP_EOL, array_merge(array_values($this->getters), array_values($this->setters)));
    }

    public function renderGetters(): string
    {
        return PHP_EOL . implode(PHP_EOL, $this->getters);
    }

    public function renderResourceAttribute(?Component $component): string
    {
        if ($component === null) {
            return '';
        }

        return <<<ATTR
#[Resource(entity: {$component->getClassString()})]
    
ATTR;
    }

    public function renderSetters(): string
    {
        return PHP_EOL . implode(PHP_EOL, $this->setters);
    }

    public function withComponent(
        Component $component,
        VisibilityEnum $visibility = VisibilityEnum::Public,
        bool $nullable = false,
        mixed $default = null,
    ): self {
        return $this
            ->withComponentGetter($component, $visibility, $nullable)
            ->withComponentSetter($component, $visibility, $nullable, $default);
    }

    public function withComponentGetter(
        Component $component,
        VisibilityEnum $visibility = VisibilityEnum::Protected,
        bool $nullable = false,
    ): self {
        $nullable = $nullable ? '?' : '';

        $this->getters[$component->getFqcn()] = <<<GTR

    $visibility->value function {$component->getGetterName()}(): $nullable{$component->getClassName()}
    {
        return \$this->{$component->getPropertyName()};
    }
GTR;

        return $this;
    }

    public function withComponentSetter(
        Component $component,
        VisibilityEnum $visibility = VisibilityEnum::Public,
        bool $nullable = false,
        mixed $default = null,
    ): self {
        $nullable = $nullable ? '?' : '';
        $default  = $default ? sprintf(' = %s', $default) : '';

        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->setters[$component->getFqcn()] = <<<STR

    $visibility->value function {$component->getSetterName()}($nullable{$component->getClassName()} \${$component->getPropertyName()}$default): self
    {
        \$this->{$component->getPropertyName()} = \${$component->getPropertyName()};

        return \$this;
    }
STR;
        // phpcs:enable Generic.Files.LineLength.TooLong

        return $this;
    }
}
