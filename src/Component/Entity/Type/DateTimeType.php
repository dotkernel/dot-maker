<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use DateTimeInterface;
use Dot\Maker\Component\Entity\Types;

class DateTimeType extends AbstractField
{
    protected ?string $phpType = DateTimeInterface::class;
    protected string $doctrineType = Types::DATETIME;
}
