<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity;

use Dot\Maker\Component\Entity\Type\ArrayType;
use Dot\Maker\Component\Entity\Type\AsciiStringType;
use Dot\Maker\Component\Entity\Type\BigIntType;
use Dot\Maker\Component\Entity\Type\BinaryType;
use Dot\Maker\Component\Entity\Type\BooleanType;
use Dot\Maker\Component\Entity\Type\FieldInterface;
use Dot\Maker\Component\Entity\Type\DateType;
use Dot\Maker\Component\Entity\Type\DateImmutableType;
use Dot\Maker\Component\Entity\Type\DateTimeType;
use Dot\Maker\Component\Entity\Type\DateTimeImmutableType;
use Dot\Maker\Component\Entity\Type\DateTimeTzType;
use Dot\Maker\Component\Entity\Type\DateTimeTzImmutableType;
use Dot\Maker\Component\Entity\Type\DecimalType;
use Dot\Maker\Component\Entity\Type\FloatType;
use Dot\Maker\Component\Entity\Type\GuidType;
use Dot\Maker\Component\Entity\Type\IntegerType;
use Dot\Maker\Component\Entity\Type\JsonType;
use Dot\Maker\Component\Entity\Type\ObjectType;
use Dot\Maker\Component\Entity\Type\SimpleArrayType;
use Dot\Maker\Component\Entity\Type\SmallIntType;
use Dot\Maker\Component\Entity\Type\StringType;
use Dot\Maker\Component\Entity\Type\TextType;
use Dot\Maker\Component\Entity\Type\TimeType;
use Dot\Maker\Component\Entity\Type\TimeImmutableType;
use Exception;

class Types
{
    public const ARRAY = 'array';
    public const ASCII_STRING = 'ascii_string';
    public const BIGINT = 'bigint';
    public const BINARY = 'binary';
    public const BLOB = 'blob';
    public const BOOLEAN = 'boolean';
    public const DATE = 'date';
    public const DATE_IMMUTABLE = 'date_immutable';
    public const DATEINTERVAL = 'dateinterval';
    public const DATETIME = 'datetime';
    public const DATETIME_IMMUTABLE = 'datetime_immutable';
    public const DATETIMETZ = 'datetimetz';
    public const DATETIMETZ_IMMUTABLE = 'datetimetz_immutable';
    public const DECIMAL = 'decimal';
    public const FLOAT = 'float';
    public const GUID = 'guid';
    public const INTEGER = 'integer';
    public const JSON = 'json';
    public const OBJECT = 'object';
    public const SIMPLE_ARRAY = 'simple_array';
    public const SMALLINT = 'smallint';
    public const STRING = 'string';
    public const TEXT = 'text';
    public const TIME = 'time';
    public const TIME_IMMUTABLE = 'time_immutable';

    protected static array $types = [
        self::ARRAY => ArrayType::class,
        self::ASCII_STRING => AsciiStringType::class,
        self::BIGINT => BigIntType::class,
        self::BINARY => BinaryType::class,
        self::BOOLEAN => BooleanType::class,
        self::DATE => DateType::class,
        self::DATE_IMMUTABLE => DateImmutableType::class,
        self::DATEINTERVAL => DateImmutableType::class,
        self::DATETIME => DateTimeType::class,
        self::DATETIME_IMMUTABLE => DateTimeImmutableType::class,
        self::DATETIMETZ => DateTimeTzType::class,
        self::DATETIMETZ_IMMUTABLE => DateTimeTzImmutableType::class,
        self::DECIMAL => DecimalType::class,
        self::FLOAT => FloatType::class,
        self::GUID => GuidType::class,
        self::INTEGER => IntegerType::class,
        self::JSON => JsonType::class,
        self::OBJECT => ObjectType::class,
        self::SIMPLE_ARRAY => SimpleArrayType::class,
        self::SMALLINT => SmallIntType::class,
        self::STRING => StringType::class,
        self::TEXT => TextType::class,
        self::TIME => TimeType::class,
        self::TIME_IMMUTABLE => TimeImmutableType::class,
    ];

    /**
     * @throws Exception
     */
    public static function fromString(string $type): FieldInterface
    {
        $class = self::$types[$type] ?? null;
        if (!class_exists($class)) {
            throw new Exception(
                sprintf('Invalid property type specified: %s', $type)
            );
        }

        return new $class;
    }

    public static function exists(string $type): bool
    {
        return array_key_exists($type, self::$types);
    }

    public static function getArrayTypes(): array
    {
        return [
            self::ARRAY,
            self::JSON,
            self::SIMPLE_ARRAY,
        ];
    }

    public static function getBitTypes(): array
    {
        return [
            self::BOOLEAN,
        ];
    }

    public static function getDateAndTimeTypes(): array
    {
        return [
            self::DATE,
            self::DATETIME,
            self::TIME,
            self::DATE_IMMUTABLE,
            self::DATETIME_IMMUTABLE,
            self::TIME_IMMUTABLE,
            self::DATEINTERVAL,
            self::DATETIMETZ,
            self::DATETIMETZ_IMMUTABLE,
        ];
    }

    public static function getNumericTypes(): array
    {
        return [
            self::INTEGER,
            self::FLOAT,
            self::DECIMAL,
            self::SMALLINT,
            self::BIGINT,
        ];
    }

    public static function getObjectTypes(): array
    {
        return [
            self::OBJECT,
        ];
    }

    public static function getStringTypes(): array
    {
        return [
            self::STRING,
            self::TEXT,
            self::BLOB,
            self::ASCII_STRING,
            self::GUID,
            self::BINARY,
        ];
    }
}
