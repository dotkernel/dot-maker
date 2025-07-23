<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Method;

use Dot\Maker\Component\Method;
use Dot\Maker\Component\ParameterInterface;

class Getter extends Method
{
    private ParameterInterface $target;

    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    public function render(): string
    {
        $nullable = $this->nullable ? '?' : '';

        return <<<GTR

    {$this->visibility->value} function $this->name(): $nullable{$this->returnType}
    {
        return \$this->{$this->target->name};
    }
GTR;
    }

    public function setTarget(ParameterInterface $target): self
    {
        $this->returnType = $target->getType();
        $this->target     = $target;

        return $this;
    }
}
