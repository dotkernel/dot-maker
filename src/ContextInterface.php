<?php

declare(strict_types=1);

namespace Dot\Maker;

interface ContextInterface
{
    public const NAMESPACE_API  = 'Api';
    public const NAMESPACE_CORE = 'Core';

    public function getProjectType(): string;

    public function getRootNamespace(): ?string;

    public function hasCore(): bool;

    public function isApi(): bool;
}
