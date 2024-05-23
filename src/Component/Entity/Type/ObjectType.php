<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use Dot\Maker\Component\Entity\Types;

class ObjectType extends AbstractField
{
    protected ?string $phpType = 'object';
    protected string $doctrineType = Types::OBJECT;
}
