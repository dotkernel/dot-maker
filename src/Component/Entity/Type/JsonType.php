<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Type;

use Dot\Maker\Component\Entity\Types;

class JsonType extends AbstractField
{
    protected ?string $phpType = 'array';
    protected string $doctrineType = Types::JSON;
}
