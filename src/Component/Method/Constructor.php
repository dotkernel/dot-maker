<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Method;

use Dot\Maker\Component;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\ParameterInterface;
use Dot\Maker\Component\PromotedProperty;

use function array_map;
use function count;
use function implode;
use function sprintf;

use const PHP_EOL;

class Constructor extends Method
{
    public function __construct()
    {
        parent::__construct(__FUNCTION__);

        $this->returnType = '';
    }

    public function addPromotedProperty(PromotedProperty $property): self
    {
        $this->parameters[] = $property;

        return $this;
    }

    public function addPromotedPropertyFromComponent(
        Component $component,
        bool $nullable = false,
        mixed $default = null
    ): self {
        $this->parameters[] = new PromotedProperty(
            $component->getPropertyName(true),
            $component->getClassName(),
            $nullable,
            $default
        );

        return $this;
    }

    public function renderParameters(int $spaces = 0): string
    {
        if (count($this->parameters) === 0) {
            return '';
        }

        $parameters = array_map(
            fn (ParameterInterface $parameter) => sprintf('        %s', $parameter->render()),
            $this->parameters
        );

        return implode(PHP_EOL, $parameters);
    }
}
