<?php

declare(strict_types=1);

namespace Dot\Maker;

class EntityPropertyTypeMapping
{
    const RELATION_ONE_TO_ONE = 'OneToOne';
    const RELATION_ONE_TO_MANY = 'OneToMany';
    const RELATION_MANY_TO_ONE = 'ManyToOne';
    const RELATION_MANY_TO_MANY = 'ManyToMany';

    const TYPE_ARRAY = 'array';
    const TYPE_ASCII_STRING = 'ascii_string';
    const TYPE_BIGINT = 'bigint';
    const TYPE_BINARY = 'binary';
    const TYPE_BLOB = 'blob';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATE = 'date';
    const TYPE_DATE_IMMUTABLE = 'date_immutable';
    const TYPE_DATEINTERVAL = 'dateinterval';
    const TYPE_DATETIME = 'datetime';
    const TYPE_DATETIME_IMMUTABLE = 'datetime_immutable';
    const TYPE_DATETIMETZ = 'datetimetz';
    const TYPE_DATETIMETZ_IMMUTABLE = 'datetimetz_immutable';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_FLOAT = 'float';
    const TYPE_GUID = 'guid';
    const TYPE_INTEGER = 'integer';
    const TYPE_JSON = 'json';
    const TYPE_OBJECT = 'object';
    const TYPE_SIMPLE_ARRAY = 'simple_array';
    const TYPE_SMALLINT = 'smallint';
    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_TIME = 'time';
    const TYPE_TIME_IMMUTABLE = 'time_immutable';

    public function getRelations(): array
    {
        return [
            self::RELATION_ONE_TO_ONE,
            self::RELATION_ONE_TO_MANY,
            self::RELATION_MANY_TO_ONE,
            self::RELATION_MANY_TO_MANY,
        ];
    }

    public function getArrayTypes(): array
    {
        return [
            self::TYPE_ARRAY,
            self::TYPE_SIMPLE_ARRAY,
            self::TYPE_JSON,
        ];
    }

    public function getBitTypes(): array
    {
        return [
            self::TYPE_BOOLEAN,
        ];
    }

    public function getDateAndTimeTypes(): array
    {
        return [
            self::TYPE_DATE,
            self::TYPE_DATE_IMMUTABLE,
            self::TYPE_DATETIME,
            self::TYPE_DATETIME_IMMUTABLE,
            self::TYPE_DATETIMETZ,
            self::TYPE_DATETIMETZ_IMMUTABLE,
            self::TYPE_TIME,
            self::TYPE_TIME_IMMUTABLE,
            self::TYPE_DATEINTERVAL,
        ];
    }

    public function getNumericTypes(): array
    {
        return [
            self::TYPE_SMALLINT,
            self::TYPE_INTEGER,
            self::TYPE_BIGINT,
            self::TYPE_DECIMAL,
            self::TYPE_FLOAT,
        ];
    }

    public function getObjectTypes(): array
    {
        return [
            self::TYPE_OBJECT,
        ];
    }

    public function getStringTypes(): array
    {
        return [
            self::TYPE_STRING,
            self::TYPE_ASCII_STRING,
            self::TYPE_TEXT,
            self::TYPE_GUID,
            self::TYPE_BINARY,
            self::TYPE_BLOB,
        ];
    }

    public function typeExists(string $type): bool
    {
        return in_array($type, $this->getRelations());
    }
}
