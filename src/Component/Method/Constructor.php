<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Method;

use Dot\Maker\Component;
use Dot\Maker\Component\Method;
use Dot\Maker\Component\PromotedProperty;

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
            $component->toCamelCase(true),
            $component->getClassName(),
            $nullable,
            $default
        );

        return $this;
    }
}
