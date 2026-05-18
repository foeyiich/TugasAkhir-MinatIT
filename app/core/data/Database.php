<?php

namespace TugasAkhir\core\data;

use InvalidArgumentException;
use PDO;
use PDOException;
use TugasAkhir\utility\UtilityClass;

/**
 * Class Database
 * * Provides a lightweight abstraction layer for database operations using PDO.
 */
final class Database
{

    private ?PDO $connection = null;

    public function getConnection(): ?PDO
    {
        return $this->connection;
    }

    /**
     * Database constructor.
     * * Initializes a new PDO connection and sets the error mode to throw exceptions.
     *
     * @param string $dsn The Data Source Name, or DSN, containing the information required to connect to the database.
     * @param string|null $user The user name for the DSN string.
     * @param string|null $pass The password for the DSN string.
     * @throws PDOException On error
     */
    private function __construct(string $dsn, ?string $user = null, ?string $pass = null)
    {
        try {
            $this->connection = new PDO($dsn, $user, $pass);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection Error: " . $e->getMessage());
        }
    }

    /**
     * Creates a new Database instance using a generic DSN.
     *
     * @param string $dataSourceName The DSN string.
     * @param string|null $user The database username.
     * @param string|null $pass The database password.
     * @return self A new instance of the Database class.
     * @throws PDOException On error
     */
    public static function create(string $dataSourceName, ?string $user = null, ?string $pass = null): self
    {
        return new self($dataSourceName, $user, $pass);
    }

    /**
     * Creates a new Database instance specifically for SQLite.
     *
     * @param string $filePath The relative file path to the SQLite database file from the PROJECT_ROOT.
     * @return self A new instance of the Database class.
     * @throws PDOException On error
     */
    public static function createSqlite(string $filePath): self
    {
        $dataSourceName = "sqlite:" . PROJECT_ROOT . DIRECTORY_SEPARATOR . $filePath;
        return self::create($dataSourceName);
    }

    /**
     * Creates a new Database instance specifically for MySQL.
     *
     * @param string $host The database host (e.g., localhost or an IP address).
     * @param string $name The database name.
     * @param string $user The database username.
     * @param string $pass The database password.
     * @return self A new instance of the Database class.
     * @throws PDOException On error
     */
    public static function createMySql(string $host, string $name, string $user, string $pass): self
    {
        $dataSourceName = "mysql:host=$host;dbname=$name;charset=utf8mb4";
        return self::create($dataSourceName, $user, $pass);
    }

    /**
     * Creates a new table in the database.
     *
     * @param string $tableName The name of the table to create.
     * @param array<string, string> $fields An associative array mapping column names to their SQL definitions (e.g., ['id' => 'INT AUTO_INCREMENT']).
     * @param bool $replace If true, drops the table before creating it. If false, uses 'IF NOT EXISTS'.
     * @return false|int Returns the number of affected rows, or false on failure.
     * @throws InvalidArgumentException If the $fields array is not an associative map.
     * @throws PDOException On creation error
     */
    public function createTable(string $tableName, array $fields, bool $replace = false): false|int
    {
        UtilityClass::validateMapArray($fields);

        $conn = $this->connection;
        $columnStrings = [];
        foreach ($fields as $columnName => $definition) {
            $columnStrings[] = "`$columnName` $definition";
        }
        $columnsSql = implode(", ", $columnStrings);

        if ($replace) {
            $this->dropTable($tableName);
        }

        $ifNotExists = $replace ? "" : "IF NOT EXISTS";
        $sql = "CREATE TABLE $ifNotExists `$tableName` ($columnsSql)";

        return $conn->exec($sql);
    }

    /**
     * Drops a table from the database if it exists.
     *
     * @param string $tableName The name of the table to drop.
     * @return false|int Returns the number of affected rows, or false on failure.
     * @throws PDOException On database execution error
     */
    public function dropTable(string $tableName): false|int
    {
        return $this->connection->exec("DROP TABLE IF EXISTS `$tableName` ");
    }

    /**
     * Inserts a new record into the specified table.
     *
     * @param string $tableName The name of the table.
     * @param array<string, mixed> $data An associative array mapping column names to their respective values to insert.
     * @return bool True on success, false on failure.
     * @throws InvalidArgumentException If the $data array is not an associative map.
     * @throws PDOException On execution error.
     */
    public function insert(string $tableName, array $data): bool
    {
        UtilityClass::validateMapArray($data);
        $conn = $this->connection;

        $columns = implode(", ", array_keys($data));
        $placeholders = $data
                |> count(...)
                |> (static fn($x) => array_fill(0, $x, '?'))
                |> (static fn($x) => implode(", ", $x));

        $sql = "INSERT INTO $tableName ($columns) VALUES ($placeholders)";

        return $conn->prepare($sql)->execute(array_values($data));
    }

    /**
     * Selects records from the database based on specific conditions.
     *
     * @param string $tableName The name of the table to query.
     * @param array<string, mixed> $where An associative array of column-value pairs to filter the results (WHERE clause).
     * @param list<string>|string $data The columns to retrieve. Can be a comma-separated string or a list of column names. Defaults to '*'.
     * @param int $limit The maximum number of records to return. Default = 25.
     * @return array<int, array<string, mixed>> A list of associative arrays representing the fetched rows.
     * @throws InvalidArgumentException If limit <= 0, or if arrays provided violate the map/list constraints.
     * @throws PDOException On execution error.
     */
    public function select(string $tableName, array $where, array|string $data = "*", int $limit = 25): array
    {
        UtilityClass::validateMapArray($where);
        if (empty($where)) {
            return $this->selectAll($tableName, $data, $limit);
        }
        if ($limit <= 0) {
            throw new InvalidArgumentException("Limit must be greater than 0");
        }

        $whereClause = self::prepareSqlPairs(" AND ", array_keys($where));

        if (is_array($data)) {
            UtilityClass::validateListArray($data);
            $dataSql = implode(", ", $data);
        } else {
            $dataSql = $data;
        }

        $sql = "SELECT $dataSql FROM $tableName WHERE $whereClause LIMIT $limit";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute(array_values($where));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Selects all records from the database up to a specified limit.
     *
     * @param string $tableName The name of the table to query.
     * @param list<string>|string $data The columns to retrieve. Can be a comma-separated string or a list of column names. Default = '*'.
     * @param int $limit The maximum number of records to return. Defaults to 100.
     * @return array<int, array<string, mixed>> A list of associative arrays representing the fetched rows.
     * @throws InvalidArgumentException If $limit <= 0 or if the $data array is not a sequential list.
     * @throws PDOException On execution error.
     */
    public function selectAll(string $tableName, array|string $data = "*", int $limit = 100): array
    {
        if ($limit <= 0) {
            throw new InvalidArgumentException("Limit must be greater than 0");
        }

        if (is_array($data)) {
            UtilityClass::validateListArray($data);
            $dataSql = implode(", ", $data);
        } else {
            $dataSql = $data;
        }

        $sql = "SELECT $dataSql FROM $tableName LIMIT $limit";
        return $this->connection->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Updates existing records in the database matching specific conditions.
     *
     * @param string $tableName The name of the table to update.
     * @param array<string, mixed> $set An associative array of column-value pairs representing the new data.
     * @param array<string, mixed> $where An associative array of column-value pairs for the WHERE clause.
     * @return bool True on success, false on failure.
     * @throws InvalidArgumentException If either $set or $where array is not an associative map.
     * @throws PDOException On execution error.
     */
    public function update(string $tableName, array $set, array $where): bool
    {
        UtilityClass::validateMapArray($set);
        UtilityClass::validateMapArray($where);

        $setSql = self::prepareSqlPairs(", ", array_keys($set));
        $whereSql = self::prepareSqlPairs(" AND ", array_keys($where));

        $query = "UPDATE $tableName SET $setSql WHERE $whereSql";

        $stmt = $this->connection->prepare($query);

        $values = array_merge(array_values($set), array_values($where));

        return $stmt->execute($values);
    }

    /**
     * Deletes records from the database matching specific conditions.
     *
     * @param string $tableName The name of the table.
     * @param array<string, mixed> $where An associative array of column-value pairs for the WHERE clause.
     * @return bool True on success, false on failure.
     * @throws InvalidArgumentException If the $where array is not an associative map.
     * @throws PDOException On execution error.
     */
    public function delete(string $tableName, array $where): bool
    {
        UtilityClass::validateMapArray($where);
        $whereClause = self::prepareSqlPairs(" AND ", array_keys($where));

        $sql = "DELETE FROM $tableName WHERE $whereClause";
        return $this->connection->prepare($sql)->execute(array_values($where));
    }

    /**
     * Checks if any records exist matching the specified conditions.
     *
     * @param string $tableName The name of the table.
     * @param array<string, mixed> $where An associative array of column-value pairs for the WHERE clause.
     * @return bool True if at least one matching record exists, false otherwise.
     */
    public function exists(string $tableName, array $where): bool
    {
        return !empty($this->select($tableName, $where, '*', 1));
    }

    /**
     * Executes a raw SQL query directly without preparing statements.
     * * WARNING: Only use this for internal queries without user input to prevent SQL injection.
     *
     * @param string $query The raw SQL query string.
     * @return bool True on success, false on failure.
     */
    public function exec(string $query): bool
    {
        return $this->connection->exec($query);
    }

    /**
     * Prepares a parameterized SQL string by joining columns with a separator.
     * * Example: ['id', 'name'] with separator ' AND ' becomes 'id = ? AND name = ?'
     *
     * @param string $separator The string used to join the SQL parts (e.g., ', ' or ' AND ').
     * @param list<string> $data A list of column names.
     * @return string The generated SQL parameterized string.
     */
    private static function prepareSqlPairs(string $separator, array $data): string
    {
        $parts = [];
        foreach ($data as $column) {
            $parts[] = "`$column` = ?";
        }
        return implode($separator, $parts);
    }

}
