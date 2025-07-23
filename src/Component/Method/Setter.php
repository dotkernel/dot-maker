<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Method;

use Dot\Maker\Component\Method;
use Dot\Maker\Component\ParameterInterface;

class Setter extends Method
{
    private ParameterInterface $target;

    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->returnType = 'self';
    }

    public function render(): string
    {
        $nullable = $this->nullable ? '?' : '';

        return <<<STR
{$this->visibility->value} function $this->name($nullable$this->target->type \$$this->target->name): {$this->returnType}
    {
        \$this->$this->target->name = \$$this->target->name;

        return \$this;
    }
STR;
    }

    public function setTarget(ParameterInterface $target): self
    {
        $this->target = $target;

        return $this;
    }
}
