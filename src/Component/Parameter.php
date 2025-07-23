<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\Component\Method\Getter;
use Dot\Maker\Component\Method\Setter;

use function sprintf;
use function ucfirst;

class Parameter implements ParameterInterface
{
    public function __construct(
        readonly public string $name,
        readonly public string $type,
        protected bool $nullable = false,
        protected ?string $default = null,
    ) {
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function getGetter(): Getter
    {
        return (new Getter(sprintf('get%s', ucfirst($this->name))))
            ->setNullable($this->nullable)
            ->setTarget($this);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSetter(): Setter
    {
        return (new Setter(sprintf('set%s', ucfirst($this->name))))
            ->setNullable($this->nullable)
            ->setTarget($this);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function render(): string
    {
        $parameter = sprintf('%s $%s', $this->type, $this->name);
        if ($this->nullable) {
            $parameter = sprintf('?%s', $parameter);
        }
        if ($this->default !== null) {
            $parameter = sprintf('%s = %s', $parameter, $this->default);
        }

        return $parameter;
    }

    public function setNullable(bool $nullable): self
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function setDefault(?string $default): self
    {
        $this->default = $default;

        return $this;
    }
}
