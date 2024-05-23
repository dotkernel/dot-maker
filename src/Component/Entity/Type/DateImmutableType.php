<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use DateTimeImmutable;
use Dot\Maker\Component\Entity\Types;

class DateImmutableType extends AbstractField
{
    protected ?string $phpType = DateTimeImmutable::class;
    protected string $doctrineType = Types::DATE_IMMUTABLE;
}
