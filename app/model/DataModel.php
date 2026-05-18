<?php

namespace TugasAkhir\model;

use InvalidArgumentException;
use PDOException;
use TugasAkhir\core\data\Database;

/**
 * Abstract Class DataModel
 *
 * Provides a base ActiveRecord-like interface for database operations.
 * All model classes representing a database table should extend this class.
 */
abstract class DataModel
{

    /**
     * Retrieves the database instance connection.
     *
     * @return Database The database connection instance.
     */
    abstract protected static function getDatabase(): Database;

    /**
     * Gets the associated database table name for the model.
     *
     * @return string The table name.
     */
    abstract protected static function getTableName(): string;

    /**
     * Gets the database schema definition for table creation.
     *
     * @return  array<string, string> An associative array mapping column names to their SQL definitions (e.g., ['id' => 'INT AUTO_INCREMENT']).
     */
    abstract protected static function getSchema(): array;

    /**
     * Initializes the model by ensuring a database connection exists
     * and automatically creating the table if it does not already exist.
     *
     * @return void
     */
    public static function init(): void
    {
        if (is_null(static::getDatabase())) {
            die("Database connection for class " . static::class . " not set");
        }
        static::getDatabase()->createTable(static::getTableName(), static::getSchema());
    }

    /**
     * Updates existing records in the database table based on specific conditions.
     *
     * @param array<string, mixed> $set – An associative array of column-value pairs representing the new data.
     * @param array<string, mixed> $where – An associative array of column-value pairs for the WHERE clause.
     * @return bool True on success, false on failure.
     * @throws InvalidArgumentException If either set or where array is not an associative map.
     * @throws PDOException On execution error
     */
    public static function update(array $set, array $where): bool
    {
        return static::getDatabase()->update(static::getTableName(), $set, $where);
    }

    /**
     * Inserts a new record into the database table.
     *
     * @param array<string, mixed> $data – An associative array mapping column names to their respective values to insert.
     * @return bool True on success, false on failure.
     * @throws InvalidArgumentException If $data is not an associative map.
     * @throws PDOException On execution error
     */
    public static function insert(array $data): bool
    {
        return static::getDatabase()->insert(static::getTableName(), $data);
    }

    /**
     * Selects records from the database table based on specific conditions.
     *
     * @param array<string, mixed> $where – An associative array of column-value pairs to filter the results (WHERE clause).
     * @param list<string>|string $data – The columns to retrieve. Can be a comma-separated string or a list of column names. Defaults to '*'.
     * @param int $limit – The maximum number of records to return. Default = 25.
     * @return array A list of associative arrays representing the fetched rows.
     * @throws InvalidArgumentException If either set or where array is not an associative map.
     * @throws PDOException On execution error
     */
    public static function select(array $where, array|string $data = "*", int $limit = 25): array
    {
        return static::getDatabase()->select(static::getTableName(), $where, $data, $limit);
    }

    /**
     * Selects all records from the database table up to a specified limit.
     *
     * @param int $limit The maximum number of records to return. Defaults to 25.
     * @return array<int, array<string, mixed>> A list of associative arrays representing the fetched rows.
     * @throws InvalidArgumentException If either set or where array is not an associative map.
     * @throws PDOException On execution error
     */
    public static function selectAll(int $limit = 25): array
    {
        return static::getDatabase()->selectAll(static::getTableName(), "*", $limit);
    }

    /**
     * Deletes records from the database table matching specific conditions.
     *
     * @param array<string, mixed> $where – An associative array of column-value pairs for the WHERE clause
     * @return bool True on success, false on failure.
     * @throws InvalidArgumentException If either set or where array is not an associative map.
     * @throws PDOException On execution error
     */
    public static function delete(array $where): bool
    {
        return static::getDatabase()->delete(static::getTableName(), $where);
    }

    /**
     * Checks if any records exist in the table that match the given conditions.
     *
     * @param array<string, mixed> $where – An associative array of column-value pairs for the WHERE clause.
     * @return bool True if at least one matching record exists, false otherwise.
     * @throws InvalidArgumentException If either set or where array is not an associative map.
     * @throws PDOException On execution error
     */
    public static function exists(array $where): bool
    {
        return static::getDatabase()->exists(static::getTableName(), $where);
    }


}
