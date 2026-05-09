<?php

namespace TugasAkhir\models;

use TugasAkhir\core\Database;

abstract class DataModel
{

    protected static function getDatabase(): ?Database
    {
        return null;
    }

    abstract protected static function getTableName(): string;

    abstract protected static function getSchema(): array;

    public static function init(): void
    {
        if (is_null(static::getDatabase())) {
            die("Database connection for class " . static::class . " not set");
        }
        static::getDatabase()->createTable(static::getTableName(), static::getSchema());
    }

    public static function update(array $set, array $where): bool
    {
        return static::getDatabase()->update(static::getTableName(), $set, $where);
    }

    public static function insert(array $data): bool
    {
        return static::getDatabase()->insert(static::getTableName(), $data);
    }

    public static function select(array $where, array|string $data = "*", int $limit = 25): array
    {
        return static::getDatabase()->select(static::getTableName(), $where, $data, $limit);
    }

    public static function selectAll(int $limit = 25): array
    {
        return static::getDatabase()->selectAll(static::getTableName(), $limit);
    }

    public static function delete(array $where): bool
    {
        return self::getDatabase()->delete(static::getTableName(), $where);
    }

    public static function exists(array $where): bool
    {
        return self::getDatabase()->exists(static::getTableName(), $where);
    }

}
