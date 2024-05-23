<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use Dot\Maker\Variable;

abstract class AbstractField implements FieldInterface
{
    protected Variable $variable;
    protected string $name;
    protected string $doctrineType;
    protected ?string $phpType = null;
    protected bool $nullable = false;
    protected bool $unique = false;
    protected ?int $length = null;
    protected ?int $precision = null;
    protected ?int $scale = null;

    public function getVariable(): Variable
    {
        return $this->variable;
    }

    public function getVariableName(): string
    {
        return $this->getVariable()->getName();
    }

    public function initVariable(string $name): self
    {
        return $this->setVariable(
            new Variable($name)
        );
    }

    public function setVariable(Variable $variable): self
    {
        $this->variable = $variable;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this->initVariable($name);
    }

    public function getDoctrineType(): string
    {
        return $this->doctrineType;
    }

    public function setDoctrineType(string $doctrineType): self
    {
        $this->doctrineType = $doctrineType;

        return $this;
    }

    public function getPhpType(): ?string
    {
        return $this->phpType;
    }

    public function setPhpType(?string $phpType): self
    {
        $this->phpType = $phpType;

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function setNullable(bool $nullable): self
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function setUnique(bool $unique): self
    {
        $this->unique = $unique;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function hasLength(): bool
    {
        return !is_null($this->length);
    }

    public function setLength(?int $length): self
    {
        $this->length = $length;

        return $this;
    }

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function hasPrecision(): bool
    {
        return !is_null($this->precision);
    }

    public function setPrecision(?int $precision): self
    {
        $this->precision = $precision;

        return $this;
    }

    public function getScale(): ?int
    {
        return $this->scale;
    }

    public function hasScale(): bool
    {
        return !is_null($this->scale);
    }

    public function setScale(?int $scale): self
    {
        $this->scale = $scale;

        return $this;
    }

    public function getDefinition(): string
    {
        $attributes = [
            sprintf('name="%s"', $this->getName()),
            sprintf('type="%s"', $this->getDoctrineType()),
        ];

        if ($this->hasLength()) {
            $attributes[] = sprintf('length=%d', $this->getLength());
        }

        if ($this->hasPrecision()) {
            $attributes[] = sprintf('precision=%d', $this->getPrecision());
        }

        if ($this->hasScale()) {
            $attributes[] = sprintf('scale=%d', $this->getScale());
        }

        if ($this->isUnique()) {
            $attributes[] = 'unique=true';
        }

        if ($this->isNullable()) {
            $attributes[] = 'nullable=true';
        }

        $attributes = implode(', ', $attributes);

        return <<<DEF
/**
     * @ORM\Column($attributes)
     */
DEF;
    }
}
