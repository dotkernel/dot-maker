<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Config;
use Dot\Maker\ContextInterface;
use Dot\Maker\FileSystem;

interface TypeInterface
{
    public function getConfig(): Config;

    public function getContext(): ContextInterface;

    public function getFileSystem(): FileSystem;

    public function getModule(): ?ModuleInterface;

    public function hasModule(): bool;

    public function isModule(): bool;

    public function setModule(ModuleInterface $module): self;
}
