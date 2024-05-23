<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use Dot\Maker\Component\Entity\Types;

class FloatType extends AbstractField
{
    protected ?string $phpType = 'float';
    protected string $doctrineType = Types::FLOAT;
}
