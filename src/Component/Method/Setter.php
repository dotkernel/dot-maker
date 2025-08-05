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

        // phpcs:disable Generic.Files.LineLength.TooLong
        return <<<STR
{$this->visibility->value} function $this->name($nullable$this->target->getType() \$$this->target->getName()): {$this->returnType}
    {
        \$this->$this->target->getName() = \$$this->target->getName();

        return \$this;
    }
STR;
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    public function setTarget(ParameterInterface $target): self
    {
        $this->target = $target;

        return $this;
    }
}
