<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use Dot\Maker\Component\Entity\Types;

class DecimalType extends AbstractField
{
    protected ?string $phpType = 'string';
    protected string $doctrineType = Types::DECIMAL;
    protected ?int $precision = 10;
    protected ?int $scale = 0;
}
