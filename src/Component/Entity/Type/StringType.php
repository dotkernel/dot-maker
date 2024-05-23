<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use Dot\Maker\Component\Entity\Types;

class StringType extends AbstractField
{
    protected ?int $length = 191;
    protected ?string $phpType = 'string';
    protected string $doctrineType = Types::STRING;
}
