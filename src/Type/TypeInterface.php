<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\Message;

interface TypeInterface
{
    public function addMessage(Message $message): static;

    public function getConfig(): Config;

    public function getContext(): Context;

    public function getFileSystem(): FileSystem;

    public function getModule(): ?ModuleInterface;

    public function hasModule(): bool;

    public function isModule(): bool;

    public function setConfig(Config $config): static;

    public function setContext(Context $context): static;

    public function setFileSystem(FileSystem $fileSystem): static;

    public function setModule(ModuleInterface $module): self;
}
