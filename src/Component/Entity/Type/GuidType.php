<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use Dot\Maker\Component\Entity\Types;

class GuidType extends AbstractField
{
    protected ?string $phpType = 'string';
    protected string $doctrineType = Types::GUID;
}
