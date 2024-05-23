<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use Dot\Maker\Component\Entity\Types;

class BinaryType extends AbstractField
{
    protected string $doctrineType = Types::BINARY;
}
