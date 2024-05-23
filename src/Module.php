<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\Config\ComponentConfigInterface;
use Exception;

class Module
{
    protected ComponentConfigInterface $componentConfig;
    protected string $fqmn; // FQMN = Fully Qualified Module Name
    protected string $name;
    protected string $namespace;
    protected string $path;

    public function __construct(ComponentConfigInterface $componentConfig)
    {
        $this->componentConfig = $componentConfig;
    }

    /**
     * @throws Exception
     */
    public function init(string $fqcn): self
    {
        return $this
            ->setFqmn(
                $this->extractFqmn($fqcn)
            )
            ->setName(
                $this->extractName($this->getFqmn())
            )
            ->setNamespace(
                $this->extractNamespace($this->getFqmn())
            )
            ->setPath(
                $this->extractPath($this->getFqmn())
            );
    }

    /**
     * @throws Exception
     */
    protected function extractFqmn(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);
        $parts = array_filter($parts);
        $parts = array_values($parts);

        $fqmn = sprintf('%s\\%s\\', $parts[0], $parts[1] ?? '');
        if ($this->getComponentConfig()->exists($fqmn)) {
            return $fqmn;
        }

        $fqmn = sprintf('%s%s\\', $fqmn, $parts[2] ?? '');
        if ($this->getComponentConfig()->exists($fqmn)) {
            return $fqmn;
        }

        $fqmn = sprintf('%s%s\\', $fqmn, $parts[3] ?? '');
        if ($this->getComponentConfig()->exists($fqmn)) {
            return $fqmn;
        }

        throw new Exception(
            sprintf(
                'Unable to identify module from FQCN [%s]. Is it registered in composer?',
                $fqcn
            )
        );
    }

    protected function extractName(string $fqmn): string
    {
        $parts = explode('\\', $fqmn);
        $parts = array_filter($parts);

        return array_pop($parts);
    }

    protected function extractNamespace(string $fqmn): string
    {
        $parts = explode('\\', $fqmn);
        $parts = array_filter($parts);
        array_pop($parts);

        return implode('\\', $parts);
    }

    protected function extractPath(string $fqmn): string
    {
        return $this->getComponentConfig()->getPathFor($fqmn);
    }

    public function getComponentConfig(): ComponentConfigInterface
    {
        return $this->componentConfig;
    }

    public function setComponentConfig(ComponentConfigInterface $componentConfig): self
    {
        $this->componentConfig = $componentConfig;

        return $this;
    }

    public function getFqmn(): string
    {
        return $this->fqmn;
    }

    protected function setFqmn(string $fqmn): self
    {
        $this->fqmn = $fqmn;

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
}
