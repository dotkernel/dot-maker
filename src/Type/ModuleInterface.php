<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Message;

interface ModuleInterface
{
    public function addMessage(Message $message): static;
}
