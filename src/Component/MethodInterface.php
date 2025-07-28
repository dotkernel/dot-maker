<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\VisibilityEnum;

interface MethodInterface
{
    public function addBodyLine(string $bodyLine, int $spaces = 8): self;

    public function addInject(Inject $inject): self;

    public function addParameter(ParameterInterface $parameter): self;

    public function getName(): string;

    public function render(): string;

    public function setBody(string $body): self;

    public function setNullable(bool $nullable): self;

    public function setReturnType(string $returnType): self;

    public function setVisibility(VisibilityEnum $visibility): self;
}
