<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Config;
use Dot\Maker\ContextInterface;
use Dot\Maker\FileSystem;
use Dot\Maker\Stub;

use function preg_match;

abstract class AbstractType implements TypeInterface
{
    protected Stub $stub;

    public function __construct(
        protected FileSystem $fileSystem,
        protected ContextInterface $context,
        protected Config $config,
        protected ?Module $module = null,
    ) {
        $this->stub = new Stub($this->config->getDefaultStubDirectory(), $this->config->getCustomStubDirectory());
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    public function getFileSystem(): FileSystem
    {
        return $this->fileSystem;
    }

    public function getModule(): ?Module
    {
        return $this->module;
    }

    public function hasModule(): bool
    {
        return $this->module instanceof Module;
    }

    public function initComponent(TypeEnum $typeEnum): FileInterface
    {
        return new ($typeEnum->getClass())(
            $this->fileSystem,
            $this->context,
            $this->config,
            $this->module,
        );
    }

    public function isModule(): bool
    {
        return false;
    }

    public function isValid(string $name): bool
    {
        return (bool) preg_match('/^[a-z0-9]+$/i', $name);
    }

    public function setModule(?ModuleInterface $module): self
    {
        $this->module = $module;

        return $this;
    }
}
