<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity;

use Dot\Maker\Component\Entity\Relation\ManyToManyRelation;
use Dot\Maker\Component\Entity\Relation\ManyToOneRelation;
use Dot\Maker\Component\Entity\Relation\OneToManyRelation;
use Dot\Maker\Component\Entity\Relation\OneToToneRelation;
use Dot\Maker\Component\Entity\Relation\RelationInterface;
use Exception;

class Relations
{
    public const ONE_TO_ONE = 'OneToOne';
    public const ONE_TO_MANY = 'OneToMany';
    public const MANY_TO_ONE = 'ManyToOne';
    public const MANY_TO_MANY = 'ManyToMany';

    protected static array $relations = [
        self::ONE_TO_ONE => OneToToneRelation::class,
        self::ONE_TO_MANY => OneToManyRelation::class,
        self::MANY_TO_ONE => ManyToOneRelation::class,
        self::MANY_TO_MANY => ManyToManyRelation::class,
    ];

    /**
     * @throws Exception
     */
    public static function fromString(string $relation): RelationInterface
    {
        $class = self::$relations[$relation] ?? null;
        if (!class_exists($class)) {
            throw new Exception(
                sprintf('Invalid relation specified: %s', $relation)
            );
        }

        return new $class;
    }

    public static function exists(string $type): bool
    {
        return array_key_exists($type, self::$relations);
    }

    public static function getRelations(): array
    {
        return [
            self::ONE_TO_ONE,
            self::ONE_TO_MANY,
            self::MANY_TO_ONE,
            self::MANY_TO_MANY,
        ];
    }

    public static function isRelation(string $type): bool
    {
        return in_array($type, self::getRelations());
    }
}
