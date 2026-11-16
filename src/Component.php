<?php

declare(strict_types=1);

namespace Dot\Maker;

use InvalidArgumentException;

use function implode;
use function in_array;
use function lcfirst;
use function preg_match;
use function preg_split;
use function sprintf;
use function str_ends_with;
use function str_replace;
use function strlen;
use function strtolower;
use function strtoupper;
use function substr;
use function ucfirst;

class Component
{
    /** @var non-empty-string $className */
    private readonly string $className;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly string $namespace,
        string $className,
    ) {
        if ($className === '') {
            throw new InvalidArgumentException('Class name must not be empty.');
        }

        $this->className = $className;
    }

    /**
     * @return non-empty-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    public function getClassString(): string
    {
        return sprintf('%s::class', $this->className);
    }

    public function getCollectionMethodName(): string
    {
        return sprintf('get%s', self::pluralize($this->className));
    }

    public function getDeleteMethodName(): string
    {
        return sprintf('delete%s', ucfirst($this->className));
    }

    public function getFindMethodName(): string
    {
        return sprintf('find%s', ucfirst($this->className));
    }

    /**
     * @return non-empty-string
     */
    public function getFqcn(): string
    {
        return sprintf('%s\\%s', $this->namespace, $this->className);
    }

    public function getGetterName(): string
    {
        return sprintf('get%s', $this->className);
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getSaveMethodName(): string
    {
        return sprintf('save%s', ucfirst($this->className));
    }

    public function getSetterName(): string
    {
        return sprintf('set%s', $this->className);
    }

    public function getVariable(bool $noInterface = true): string
    {
        return sprintf('$%s', $this->toCamelCase($noInterface));
    }

    public static function pluralize(string $name): string
    {
        if ($name === '') {
            return $name;
        }

        $lastLetter = strtolower($name[strlen($name) - 1]);

        if (in_array($lastLetter, ['s', 'x', 'z']) || preg_match('/(sh|ch)$/i', $name)) {
            return $name . 'es';
        } elseif (preg_match('/[^aeiou]y$/i', $name)) {
            return substr($name, 0, -1) . 'ies';
        } else {
            return $name . 's';
        }
    }

    /**
     * Remove $suffix from the end of $name, unless $name is exactly $suffix.
     */
    public static function stripSuffix(string $name, string $suffix): string
    {
        if ($name !== $suffix && str_ends_with($name, $suffix)) {
            return substr($name, 0, -strlen($suffix));
        }

        return $name;
    }

    public function toCamelCase(bool $noInterface = false): string
    {
        $property = lcfirst($this->className);
        if ($noInterface) {
            return self::stripSuffix($property, 'Interface');
        }

        return $property;
    }

    public function toKebabCase(bool $noInterface = true): string
    {
        $camelCase = $this->toCamelCase($noInterface);
        $parts     = preg_split('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', $camelCase);

        return strtolower(implode('-', $parts === false ? [$camelCase] : $parts));
    }

    public function toPlural(): string
    {
        return self::pluralize($this->className);
    }

    public function toSnakeCase(bool $noInterface = true): string
    {
        return str_replace('-', '_', $this->toKebabCase($noInterface));
    }

    public function toUpperCase(bool $noInterface = true): string
    {
        return strtoupper($this->toSnakeCase($noInterface));
    }
}
