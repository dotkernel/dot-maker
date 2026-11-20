<?php

declare(strict_types=1);

namespace Dot\Maker\Type;

use Dot\Maker\Component;
use Dot\Maker\Component\Import;
use Dot\Maker\Config;
use Dot\Maker\Context;
use Dot\Maker\Exception\RuntimeException;
use Dot\Maker\FileSystem;
use Dot\Maker\Message;

use function in_array;
use function is_array;
use function preg_match;
use function sprintf;
use function strtolower;
use function token_get_all;

use const T_STRING;

abstract class AbstractType implements TypeInterface
{
    /**
     * Type names that tokenize as T_STRING but cannot be used as class names.
     */
    private const COMPONENT_SUFFIXES = [
        'Collection',
        'Command',
        'Entity',
        'Form',
        'Handler',
        'Input',
        'InputFilter',
        'Interface',
        'Middleware',
        'Repository',
        'Service',
    ];

    private const RESERVED_TYPE_NAMES = [
        'bool',
        'false',
        'float',
        'int',
        'iterable',
        'mixed',
        'never',
        'null',
        'object',
        'parent',
        'self',
        'string',
        'true',
        'void',
    ];

    protected Import $import;

    public function __construct(
        protected FileSystem $fileSystem,
        protected Context $context,
        protected Config $config,
        protected ?ModuleInterface $module = null,
    ) {
        $this->import = new Import($context);
    }

    /**
     * @throws RuntimeException
     */
    public function addMessage(Message $message): static
    {
        if ($this->module === null) {
            throw new RuntimeException('No module selected.');
        }

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

    /**
     * @throws RuntimeException
     */
    public function component(TypeEnum $typeEnum): FileInterface
    {
        $component = new ($typeEnum->value)(
            $this->fileSystem,
            $this->context,
            $this->config,
            $this->module,
        );
        if (! $component instanceof FileInterface) {
            throw new RuntimeException(sprintf('%s cannot create files.', $typeEnum->value));
        }

        return $component;
    }

    public function isModule(): bool
    {
        return false;
    }

    /**
     * A valid name is a usable PHP class name, both as typed and with any component suffix removed,
     * because some components (for example Entity) use the name without its suffix as the class name.
     */
    public function isValid(string $name): bool
    {
        foreach (self::COMPONENT_SUFFIXES as $suffix) {
            if (! self::isClassName(Component::stripSuffix($name, $suffix))) {
                return false;
            }
        }

        return self::isClassName($name);
    }

    private static function isClassName(string $name): bool
    {
        if (preg_match('/^[a-z][a-z0-9]*$/i', $name) !== 1) {
            return false;
        }

        if (in_array(strtolower($name), self::RESERVED_TYPE_NAMES, true)) {
            return false;
        }

        // Rejects every keyword known to the running PHP version, for example List, Class, Match or Readonly.
        $tokens = token_get_all('<?php ' . $name);

        return is_array($tokens[1] ?? null) && $tokens[1][0] === T_STRING;
    }

    public function setConfig(Config $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function setContext(Context $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function setFileSystem(FileSystem $fileSystem): static
    {
        $this->fileSystem = $fileSystem;

        return $this;
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
