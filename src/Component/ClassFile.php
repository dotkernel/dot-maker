<?php

declare(strict_types=1);

namespace Dot\Maker\Component;

use function array_key_exists;
use function array_map;
use function count;
use function implode;
use function sort;
use function sprintf;

use const PHP_EOL;

class ClassFile
{
    /** @var list<Inject> $injects */
    private array $injects = [];
    /** @var array<non-empty-string, MethodInterface> $methods */
    private array $methods = [];
    /** @var array<non-empty-string, ParameterInterface> $properties */
    private array $properties = [];
    /** @var array<non-empty-string, non-empty-string> $classUses */
    private array $classUses = [];
    /** @var array<non-empty-string, non-empty-string> $constantUses */
    private array $constantUses = [];
    /** @var array<non-empty-string, non-empty-string> $functionUses */
    private array $functionUses = [];
    /** @var array<non-empty-string, non-empty-string> $interfaces */
    private array $interfaces = [];
    /** @var array<non-empty-string, non-empty-string> $traits */
    private array $traits     = [];
    private bool $abstract    = false;
    private bool $readonly    = false;
    private bool $final       = false;
    private bool $strictTypes = true;
    private ?string $extends  = null;
    private string $comment   = '';

    public function __construct(
        readonly public string $namespace,
        readonly public string $className,
    ) {
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function addInject(Inject $inject): self
    {
        $this->injects[] = $inject;

        return $this;
    }

    /**
     * @param non-empty-string $interface
     */
    public function addInterface(string $interface): self
    {
        $this->interfaces[$interface] = $interface;

        return $this;
    }

    public function addMethod(MethodInterface $method): self
    {
        $this->methods[$method->getName()] = $method;

        return $this;
    }

    public function addProperty(ParameterInterface $property): self
    {
        $this->properties[$property->getName()] = $property;

        return $this;
    }

    /**
     * @param non-empty-string $trait
     */
    public function addTrait(string $trait): self
    {
        $use = sprintf('use %s;', $trait);

        $this->traits[$use] = $use;

        return $this;
    }

    public function getInjects(): array
    {
        return $this->injects;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getClassUses(): array
    {
        return $this->classUses;
    }

    public function getConstantUses(): array
    {
        return $this->constantUses;
    }

    public function getFunctionUses(): array
    {
        return $this->functionUses;
    }

    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    public function getTraits(): array
    {
        return $this->traits;
    }

    public function isAbstract(): bool
    {
        return $this->abstract;
    }

    public function isReadonly(): bool
    {
        return $this->readonly;
    }

    public function isFinal(): bool
    {
        return $this->final;
    }

    public function isStrictTypes(): bool
    {
        return $this->strictTypes;
    }

    public function getExtends(): ?string
    {
        return $this->extends;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function hasMethod(string $method): bool
    {
        return array_key_exists($method, $this->methods);
    }

    public function render(): string
    {
        $class = '<?php' . PHP_EOL;

        if ($this->strictTypes) {
            $class .= PHP_EOL . 'declare(strict_types=1);' . PHP_EOL;
        }

        $class .= PHP_EOL . 'namespace ' . $this->namespace . ';' . PHP_EOL;

        sort($this->classUses);
        if (count($this->classUses) > 0) {
            foreach ($this->classUses as $use) {
                $class .= PHP_EOL . $use;
            }
            $class .= PHP_EOL;
        }

        sort($this->functionUses);
        if (count($this->functionUses) > 0) {
            foreach ($this->functionUses as $use) {
                $class .= PHP_EOL . $use;
            }
            $class .= PHP_EOL;
        }

        sort($this->constantUses);
        if (count($this->constantUses) > 0) {
            foreach ($this->constantUses as $use) {
                $class .= PHP_EOL . $use;
            }
            $class .= PHP_EOL;
        }

        if ($this->comment !== '') {
            $class .= PHP_EOL . $this->comment;
        }

        if (count($this->injects) > 0) {
            foreach ($this->injects as $inject) {
                $class .= PHP_EOL . $inject->render(0);
            }
        }

        $class .= PHP_EOL;
        if ($this->final) {
            $class .= 'final ';
        }
        if ($this->readonly) {
            $class .= 'readonly ';
        }
        if ($this->abstract) {
            $class .= 'abstract ';
        }
        $class .= sprintf('class %s', $this->className);
        if ($this->extends) {
            $class .= sprintf(' extends %s', $this->extends);
        }
        if (count($this->interfaces) > 0) {
            $class .= sprintf(' implements %s', implode(', ', $this->interfaces));
        }
        $class .= PHP_EOL;
        $class .= '{' . PHP_EOL;

        if (count($this->traits) > 0) {
            $traits = array_map(fn (string $trait): string => $trait, $this->traits);

            $class .= '    ' . implode(PHP_EOL . '    ', $traits) . PHP_EOL . PHP_EOL;
        }

        if (count($this->properties) > 0) {
            $properties = array_map(
                fn (ParameterInterface $property): string => $property->render(4),
                $this->properties
            );

            $class .= implode(PHP_EOL, $properties) . PHP_EOL . PHP_EOL;
        }

        if (count($this->methods) > 0) {
            $methods = array_map(fn (MethodInterface $method): string => $method->render(), $this->methods);

            $class .= '    ' . implode(PHP_EOL . PHP_EOL . '    ', $methods) . PHP_EOL;
        }

        $class .= '}' . PHP_EOL;

        return $class;
    }

    public function setAbstract(bool $abstract): self
    {
        $this->abstract = $abstract;

        return $this;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function setExtends(string $extends): self
    {
        $this->extends = $extends;

        return $this;
    }

    public function setFinal(bool $final): self
    {
        $this->final = $final;

        return $this;
    }

    public function setReadonly(bool $readonly): self
    {
        $this->readonly = $readonly;

        return $this;
    }

    public function setStrictTypes(bool $strictTypes): self
    {
        $this->strictTypes = $strictTypes;

        return $this;
    }

    /**
     * @param non-empty-string $use
     * @param non-empty-string|null $alias
     */
    public function useClass(string $use, ?string $alias = null): self
    {
        if ($alias !== null) {
            $use = sprintf('use %s as %s;', $use, $alias);
        } else {
            $use = sprintf('use %s;', $use);
        }

        $this->classUses[$use] = $use;

        return $this;
    }

    /**
     * @param non-empty-string $use
     */
    public function useFunction(string $use): self
    {
        $use = sprintf('use function %s;', $use);

        $this->functionUses[$use] = $use;

        return $this;
    }

    /**
     * @param non-empty-string $use
     */
    public function useConstant(string $use): self
    {
        $use = sprintf('use const %s;', $use);

        $this->constantUses[$use] = $use;

        return $this;
    }
}
