<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use Dot\Maker\Component\Entity\Types;

class IntegerType extends AbstractField
{
    protected ?string $phpType = 'int';
    protected string $doctrineType = Types::INTEGER;
}
