<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\Config\ComponentConfigInterface;
use Exception;

class Dependency
{
    public const TYPE_ARRAY = 'array';
    protected ComponentConfigInterface $componentConfig;
    protected Module $module;
    protected Variable $variable;
    protected string $fqcn = '';
    protected string $name = '';
    protected string $namespace = '';
    protected string $originalName = '';
    protected string $path = '';
    protected string $type = '';

    public function __construct(ComponentConfigInterface $componentConfig)
    {
        $this->setComponentConfig($componentConfig);
    }

    public function getComponentConfig(): ComponentConfigInterface
    {
        return $this->componentConfig;
    }

    protected function setComponentConfig(ComponentConfigInterface $componentConfig): self
    {
        $this->componentConfig = $componentConfig;

        return $this;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function setModule(Module $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getVariable(): Variable
    {
        return $this->variable;
    }

    public function setVariable(Variable $variable): self
    {
        $this->variable = $variable;

        return $this;
    }

    public function getFqcn(): string
    {
        return $this->fqcn;
    }

    protected function setFqcn(string $fqcn): self
    {
        $this->fqcn = $fqcn;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    protected function setOriginalName(string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    protected function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    protected function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    protected function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function isArray(): bool
    {
        return $this->type === self::TYPE_ARRAY;
    }

    /**
     * @throws Exception
     */
    public function init(string $fqcn, string $alias = null): self
    {
        if (str_starts_with($fqcn, 'config')) {
            return $this
                ->setFqcn($fqcn)
                ->setName($fqcn)
                ->setOriginalName($fqcn)
                ->setType('array')
                ->setVariable(
                    new Variable($fqcn, $alias)
                );
        }

        if (!class_exists($fqcn) && !interface_exists($fqcn)) {
            throw new Exception(
                sprintf('Class (%s) does not exist.', $fqcn)
            );
        }

        return $this
            ->initModule($fqcn)
            ->setFqcn($fqcn)
            ->setOriginalName($fqcn)
            ->setName(
                $this->extractName($fqcn)
            )
            ->setNamespace(
                $this->extractNamespace($fqcn)
            )
            ->setPath(
                $this->extractPath($fqcn)
            )
            ->setType(
                $this->getName()
            )
            ->setVariable(
                new Variable($this->getName(), $alias)
            );
    }

    /**
     * @throws Exception
     */
    public function initModule(string $fqcn): self
    {
        return $this->setModule(
            (new Module($this->getComponentConfig()))->init($fqcn)
        );
    }

    protected function extractName(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);
        $parts = array_filter($parts);

        return array_pop($parts);
    }

    protected function extractNamespace(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);
        $parts = array_filter($parts);
        array_pop($parts);

        return implode('\\', $parts);
    }

    protected function extractPath(string $fqcn): string
    {
        return sprintf(
            '%s/%s.php',
            $this->getModule()->getPath(),
            str_replace($this->getModule()->getFqmn(), '', $fqcn)
        );
    }
}
