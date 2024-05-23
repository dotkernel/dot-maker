<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use DateInterval;
use Dot\Maker\Component\Entity\Types;

class DateIntervalType extends AbstractField
{
    protected ?string $phpType = DateInterval::class;
    protected string $doctrineType = Types::DATEINTERVAL;
}
