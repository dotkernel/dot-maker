<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\Config\ComponentConfigInterface;

class Stub
{
    protected ComponentConfigInterface $componentConfig;
    protected string $body;
    protected string $name;
    protected string $path;

    public function __construct(ComponentConfigInterface $componentConfig)
    {
        $this->setComponentConfig($componentConfig);
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

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function init(string $name): self
    {
        return $this
            ->setName(strtolower($name))
            ->setPath($this->findPath())
            ->setBody($this->readPath());
    }

    public function findPath(): string
    {
        $path = $this->findPublishedPath();
        if (is_readable($path)) {
            return $path;
        }

        return $this->findDefaultPath();
    }

    public function findPublishedPath(): string
    {
        return sprintf('%s/%s', $this->getComponentConfig()->getPublishedStubsDir(), $this->getName());
    }

    public function findDefaultPath(): string
    {
        return sprintf('%s/%s', $this->getComponentConfig()->getDefaultStubsDir(), $this->getName());
    }

    public function readPath(): string
    {
        return file_get_contents($this->getPath());
    }
}
