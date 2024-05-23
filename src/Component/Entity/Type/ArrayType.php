<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use Dot\Maker\Component\Entity\Types;

class ArrayType extends AbstractField
{
    protected ?string $phpType = 'array';
    protected string $doctrineType = Types::ARRAY;
}
