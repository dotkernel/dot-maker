<?php

declare(strict_types=1);

namespace Dot\Maker;

use function implode;
use function in_array;
use function lcfirst;
use function preg_match;
use function preg_replace;
use function preg_split;
use function sprintf;
use function str_replace;
use function strlen;
use function strtolower;
use function strtoupper;
use function substr;
use function ucfirst;

class Component
{
    public function __construct(
        private readonly string $namespace,
        private readonly string $className,
    ) {
    }

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
        $lastLetter = strtolower($name[strlen($name) - 1]);

        if (in_array($lastLetter, ['s', 'x', 'z']) || preg_match('/(sh|ch)$/i', $name)) {
            return $name . 'es';
        } elseif (preg_match('/[^aeiou]y$/i', $name)) {
            return substr($name, 0, -1) . 'ies';
        } else {
            return $name . 's';
        }
    }

    public function toCamelCase(bool $noInterface = false): string
    {
        $property = lcfirst($this->className);
        if ($noInterface) {
            return preg_replace('/Interface$/', '', $property);
        }

        return $property;
    }

    public function toKebabCase(bool $noInterface = true): string
    {
        $parts = preg_split('/(?<=[a-z])(?=[A-Z])|(?<=[A-Z])(?=[A-Z][a-z])/', $this->toCamelCase($noInterface));

        return strtolower(implode('-', $parts));
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
