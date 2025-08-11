<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component\Import;
use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\FileSystem;
use Dot\Maker\Message;

use function preg_match;
use function sprintf;

abstract class AbstractType implements TypeInterface
{
    protected Import $import;

    public function __construct(
        protected FileSystem $fileSystem,
        protected Context $context,
        protected Config $config,
        protected ?ModuleInterface $module = null,
    ) {
        $this->import = new Import($context);
    }

    public function addMessage(Message $message): static
    {
        $this->module->addMessage($message);

        return $this;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getFileSystem(): FileSystem
    {
        return $this->fileSystem;
    }

    public function getModule(): ?ModuleInterface
    {
        return $this->module;
    }

    public function hasModule(): bool
    {
        return $this->module instanceof ModuleInterface;
    }

    public function component(TypeEnum $typeEnum): FileInterface
    {
        return new ($typeEnum->value)(
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

    public function setModule(?ModuleInterface $module): static
    {
        $this->module = $module;

        return $this;
    }

    public static function wrap(int|string $value): string
    {
        return sprintf('\'%s\'', $value);
    }
}
