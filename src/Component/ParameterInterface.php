<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use Dot\Maker\Component\Method\Getter;
use Dot\Maker\Component\Method\Setter;

interface ParameterInterface
{
    public function getGetter(): Getter;

    public function getName(): string;

    public function getSetter(): Setter;

    public function getType(): string;
}
