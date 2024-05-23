<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use Dot\Maker\Component\Entity\Types;

class BooleanType extends AbstractField
{
    protected ?string $phpType = 'bool';
    protected string $doctrineType = Types::BOOLEAN;
}
