<?php

abstract class DataModels
{
    protected static PDO $db;

    abstract protected static function getTableName(): string;

    abstract protected static function getSchema(): array;

    public static function setConnection($connection): void
    {
        static::$db = $connection;
    }

    public static function init(): void
    {
        $tableName = static::getTableName();
        $columns = static::getSchema();

        $columnSql = [];
        foreach ($columns as $name => $definition) {
            $columnSql[] = "$name $definition";
        }
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (" . implode(", ", $columnSql) . ")";
        static::$db->exec($sql);
    }

    public static function update(array $set, array $where): bool
    {
        UtilityClass::validateMapArray($set);
        UtilityClass::validateMapArray($where);

        $tableName = static::getTableName();

        $setSql = static::buildPlaceholderString(", ", array_keys($set));
        $whereSql = static::buildPlaceholderString(" AND ", array_keys($where));

        $query = "UPDATE $tableName SET $setSql WHERE $whereSql";

        $stmt = static::$db->prepare($query);

        $values = array_merge(array_values($set), array_values($where));

        return $stmt->execute($values);
    }

    public static function insert(array $data): false|string
    {
        UtilityClass::validateMapArray($data);

        $tableName = static::getTableName();
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));

        $sql = "INSERT INTO $tableName ($columns) VALUES ($placeholders)";

        $stmt = static::$db->prepare($sql);
        $stmt->execute(array_values($data));

        return static::$db->lastInsertId();
    }

    public static function select(array $where, array|string $data = "*", int $limit = 25): array
    {
        UtilityClass::validateMapArray($where);
        if (empty($where)) {
            return static::selectAll();
        }
        if ($limit <= 0) {
            throw new InvalidArgumentException("Limit must be greater than 0");
        }

        $tableName = static::getTableName();

        $whereClause = static::buildPlaceholderString(" AND ", array_keys($where));
        if (!is_string($data)) {
            UtilityClass::validateListArray($data);
            $data = implode(", ", $data);
        }


        $sql = "SELECT $data FROM $tableName WHERE $whereClause LIMIT $limit";

        $stmt = static::$db->prepare($sql);
        $stmt->execute(array_values($where));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function selectAll(int $limit = 25): array
    {
        $tableName = static::getTableName();
        $sql = "SELECT * FROM $tableName LIMIT $limit";
        $stmt = static::$db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function delete(array $where): bool
    {
        UtilityClass::validateMapArray($where);
        $tableName = static::getTableName();
        $whereClause = static::buildPlaceholderString(" AND ", array_keys($where));

        $sql = "DELETE FROM $tableName WHERE $whereClause";
        $stmt = static::$db->prepare($sql);
        return $stmt->execute(array_values($where));
    }

    public static function exists(array $where): bool
    {
        return !empty(static::select($where, '*', 1));
    }

    private static function buildPlaceholderString(string $separator, array $data): string
    {
        $parts = [];
        foreach ($data as $column) {
            $parts[] = "$column = ?";
        }
        return implode($separator, $parts);
    }

}
