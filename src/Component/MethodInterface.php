<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\VisibilityEnum;

interface MethodInterface
{
    public function addInject(Inject $inject): self;

    public function addParameter(ParameterInterface $parameter): self;

    public function appendBody(string $string, int $spaces = 8, bool $newLine = true): self;

    public function getName(): string;

    public function render(): string;

    public function setBody(string $body): self;

    public function setNullable(bool $nullable): self;

    public function setReturnType(string $returnType): self;

    public function setVisibility(VisibilityEnum $visibility): self;
}
