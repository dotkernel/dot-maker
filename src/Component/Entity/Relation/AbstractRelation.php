<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Relation;

use Dot\Maker\Variable;

abstract class AbstractRelation implements RelationInterface
{
    protected Variable $variable;
    protected string $name;
    protected string $phpType;
    protected string $doctrineType;
    protected string $targetEntity;
    protected string $cascade = '{"persist", "remove"}';
    protected ?string $fetch;
    protected ?string $mappedBy;
    protected ?string $inversedBy;
    protected ?string $orphanRemoval;

    public function getVariable(): Variable
    {
        return $this->variable;
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

    public function getPhpType(): string
    {
        return $this->phpType;
    }

    public function setPhpType(string $phpType): self
    {
        $this->phpType = $phpType;

        return $this;
    }

    public function getTargetEntity(): string
    {
        return $this->targetEntity;
    }

    public function getTargetEntityClassName(string $targetEntity): string
    {
        $parts = explode('\\', $targetEntity);
        $parts = array_filter($parts);

        return array_pop($parts);
    }

    public function setTargetEntity(string $targetEntity): self
    {
        $this->targetEntity = $targetEntity;

        return $this->setPhpType(
            $this->getTargetEntityClassName($targetEntity)
        );
    }

    public function getCascade(): string
    {
        return $this->cascade;
    }

    public function hasCascade(): bool
    {
        return !is_null($this->cascade);
    }

    public function setCascade(string $cascade): self
    {
        $this->cascade = $cascade;

        return $this;
    }

    public function getFetch(): ?string
    {
        return $this->fetch;
    }

    public function hasFetch(): bool
    {
        return !is_null($this->fetch);
    }

    public function setFetch(?string $fetch): self
    {
        $this->fetch = $fetch;

        return $this;
    }

    public function getMappedBy(): ?string
    {
        return $this->mappedBy;
    }

    public function hasMappedBy(): bool
    {
        return !is_null($this->mappedBy);
    }

    public function setMappedBy(?string $mappedBy): self
    {
        $this->mappedBy = $mappedBy;

        return $this;
    }

    public function getInversedBy(): ?string
    {
        return $this->inversedBy;
    }

    public function hasInversedBy(): bool
    {
        return !is_null($this->inversedBy);
    }

    public function setInversedBy(?string $inversedBy): self
    {
        $this->inversedBy = $inversedBy;

        return $this;
    }

    public function getOrphanRemoval(): ?string
    {
        return $this->orphanRemoval;
    }

    public function hasOrphanRemoval(): bool
    {
        return !is_null($this->orphanRemoval);
    }

    public function setOrphanRemoval(?string $orphanRemoval): self
    {
        $this->orphanRemoval = $orphanRemoval;

        return $this;
    }

    abstract public function getDefinition(): string;
}
