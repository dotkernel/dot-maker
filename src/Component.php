<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\Component\Import;

use function lcfirst;
use function preg_replace;
use function sprintf;

class Component
{
    private Import $import;

    public function __construct(
        private readonly string $namespace,
        private readonly string $className,
    ) {
        $this->import = new Import();
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getClassString(): string
    {
        return sprintf('%s::class', $this->className);
    }

    public function getFqcn(): string
    {
        return sprintf('%s\\%s', $this->namespace, $this->className);
    }

    public function getGetterName(): string
    {
        return sprintf('get%s', $this->className);
    }

    public function getImport(): Import
    {
        return $this->import;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getPropertyName(bool $noInterface = false): string
    {
        $property = lcfirst($this->className);
        if ($noInterface) {
            return preg_replace('/Interface$/', '', $property);
        }

        return $property;
    }

    public function getSetterName(): string
    {
        return sprintf('set%s', $this->className);
    }

    public function getVariable(bool $noInterface = true): string
    {
        return sprintf('$%s', $this->getPropertyName($noInterface));
    }

    public function useClass(string $name, ?string $alias = null): self
    {
        $this->import->addClassUse($name, $alias);

        return $this;
    }

    public function useFunction(string $name): self
    {
        $this->import->addFunctionUse($name);

        return $this;
    }

    public function useConstant(string $name): self
    {
        $this->import->addConstantUse($name);

        return $this;
    }
}
