<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Relation;

use Dot\Maker\Variable;

interface RelationInterface
{
    public function getVariable(): Variable;

    public function initVariable(string $name): self;

    public function setVariable(Variable $variable): self;

    public function getName(): string;

    public function setName(string $name): self;

    public function getDoctrineType(): string;

    public function setDoctrineType(string $doctrineType): self;

    public function getPhpType(): string;

    public function setPhpType(string $phpType): self;

    public function getTargetEntity(): string;

    public function getTargetEntityClassName(string $targetEntity): string;

    public function setTargetEntity(string $targetEntity): self;

    public function getCascade(): string;

    public function hasCascade(): bool;

    public function setCascade(string $cascade): self;

    public function getFetch(): ?string;

    public function hasFetch(): bool;

    public function setFetch(?string $fetch): self;

    public function getMappedBy(): ?string;

    public function hasMappedBy(): bool;

    public function setMappedBy(?string $mappedBy): self;

    public function getInversedBy(): ?string;

    public function hasInversedBy(): bool;

    public function setInversedBy(?string $inversedBy): self;

    public function getOrphanRemoval(): ?string;

    public function hasOrphanRemoval(): bool;

    public function setOrphanRemoval(?string $orphanRemoval): self;

    public function getDefinition(): string;
}
