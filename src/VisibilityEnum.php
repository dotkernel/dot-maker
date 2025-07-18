<?php

declare(strict_types=1);

namespace Dot\Maker;

enum VisibilityEnum: string
{
    case Private   = 'private';
    case Protected = 'protected';
    case Public    = 'public';
}
