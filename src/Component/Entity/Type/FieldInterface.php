<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use Dot\Maker\Variable;

interface FieldInterface
{
    public function getVariable(): Variable;

    public function getVariableName(): string;

    public function initVariable(string $name): self;

    public function setVariable(Variable $variable): self;

    public function getName(): string;

    public function setName(string $name): self;

    public function getDoctrineType(): string;

    public function setDoctrineType(string $doctrineType): self;

    public function getPhpType(): ?string;

    public function setPhpType(?string $phpType): self;

    public function isNullable(): bool;

    public function setNullable(bool $nullable): self;

    public function isUnique(): bool;

    public function setUnique(bool $unique): self;

    public function getLength(): ?int;

    public function setLength(?int $length): self;

    public function getPrecision(): ?int;

    public function setPrecision(?int $precision): self;

    public function getScale(): ?int;

    public function setScale(?int $scale): self;

    public function getDefinition(): string;
}
