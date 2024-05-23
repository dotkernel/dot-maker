<?php

declare(strict_types=1);

namespace Dot\Maker;

use Dot\Maker\Exception\InvalidPropertyNameException;

class Variable
{
    protected ?Variable $alias = null;
    protected string $name;
    protected string $property;
    protected string $getter;
    protected string $setter;

    public function __construct(string $className, ?string $alias = null)
    {
        $this->setProperty(
            $this->extractProperty($className)
        )->setName(
            $this->extractName($this->getProperty())
        )->setGetter(
            $this->generateGetter($this->getProperty())
        )->setsetter(
            $this->generateSetter($this->getProperty())
        );
        if (!empty($alias)) {
            $this->setAlias(
                new Variable($alias)
            );
        }
    }

    public function getAlias(): ?Variable
    {
        return $this->alias;
    }

    public function setAlias(?Variable $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function hasAlias(): bool
    {
        return $this->alias instanceof Variable;
    }

    public function getName(): string
    {
        if ($this->hasAlias()) {
            return $this->getAlias()->getName();
        }

        return $this->name;
    }

    public function getRealName(): string
    {
        return $this->name;
    }

    protected function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getProperty(): string
    {
        if ($this->hasAlias()) {
            return $this->getAlias()->getProperty();
        }

        return $this->property;
    }

    public function getRealProperty(): string
    {
        return $this->property;
    }

    protected function setProperty(string $property): self
    {
        $this->property = $property;

        return $this;
    }

    public function getGetter(): string
    {
        return $this->getter;
    }

    public function setGetter(string $getter): self
    {
        $this->getter = $getter;

        return $this;
    }

    public function getSetter(): string
    {
        return $this->setter;
    }

    public function setSetter(string $setter): self
    {
        $this->setter = $setter;

        return $this;
    }

    protected function extractName(string $property): string
    {
        return sprintf('$%s', $property);
    }

    protected function extractProperty(string $name): string
    {
        // Check if it's a dot-separated config value
        if (substr_count($name, '.') > 0) {
            $name = explode('.', $name);
            $name = array_filter($name);
            $name = array_map('ucfirst', $name);
            $name = implode('', $name);
        }

        return lcfirst($name);
    }

    protected function generateGetter(string $property): string
    {
        return sprintf('get%s', ucfirst($property));
    }

    protected function generateSetter(string $property): string
    {
        return sprintf('set%s', ucfirst($property));
    }

    /**
     * @throws InvalidPropertyNameException
     */
    public static function validateName(string $name): string
    {
        preg_match('/^[a-z_].*$/i', $name, $matches);
        if (empty($matches)) {
            throw new InvalidPropertyNameException(
                'Property names must start with a letter or an underscore.'
            );
        }

        return $name;
    }
}
