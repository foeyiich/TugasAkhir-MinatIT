<?php

namespace TugasAkhir\core;

use InvalidArgumentException;
use PDO;
use PDOException;
use TugasAkhir\utils\UtilityClass;

final class Database
{

    private ?PDO $connection = null;

    public function getConnection(): ?PDO
    {
        return $this->connection;
    }

    private function __construct(string $dsn, ?string $user = null, ?string $pass = null)
    {
        try {
            $this->connection = new PDO($dsn, $user, $pass);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection Error: " . $e->getMessage());
        }
    }

    public static function createSqlite(string $filePath): Database
    {
        $dataSourceName = "sqlite:" . PROJECT_ROOT . DIRECTORY_SEPARATOR . $filePath;
        return new self($dataSourceName);
    }

    public static function createMySql(string $host, string $name, string $user, string $pass): Database
    {
        $dataSourceName = "mysql:host=$host;dbname=$name;charset=utf8mb4";
        return new self($dataSourceName, $user, $pass);
    }

    public function createTable(string $tableName, array $schema, bool $replace = false): false|int
    {
        UtilityClass::validateMapArray($schema);

        $conn = $this->getConnection();
        $columnStrings = [];
        foreach ($schema as $columnName => $definition) {
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

    public function dropTable(string $tableName): false|int
    {
        $conn = $this->getConnection();
        return $conn->exec("DROP TABLE IF EXISTS `$tableName` ");
    }

    public function insert(string $tableName, array $data): bool
    {
        UtilityClass::validateMapArray($data);
        $conn = $this->getConnection();

        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));

        $sql = "INSERT INTO $tableName ($columns) VALUES ($placeholders)";

        $stmt = $conn->prepare($sql);
        return $stmt->execute(array_values($data));
    }

    public function select(string $tableName, array $where, array|string $data = "*", int $limit = 25): array
    {
        UtilityClass::validateMapArray($where);
        if (empty($where)) {
            return $this->selectAll($tableName, $data, $limit);
        }
        if ($limit <= 0) {
            throw new InvalidArgumentException("Limit must be greater than 0");
        }

        $whereClause = Database::prepareSqlPairs(" AND ", array_keys($where));

        if (is_array($data)) {
            UtilityClass::validateListArray($data);
            $dataSql = implode(", ", $data);
        } else {
            $dataSql = $data;
        }

        $sql = "SELECT $dataSql FROM $tableName WHERE $whereClause LIMIT $limit";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute(array_values($where));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectAll(string $tableName, int $limit = 100): array
    {
        $sql = "SELECT * FROM $tableName LIMIT $limit";
        $stmt = $this->getConnection()->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function update(string $tableName, array $set, array $where): bool
    {
        UtilityClass::validateMapArray($set);
        UtilityClass::validateMapArray($where);

        $setSql = Database::prepareSqlPairs(", ", array_keys($set));
        $whereSql = Database::prepareSqlPairs(" AND ", array_keys($where));

        $query = "UPDATE $tableName SET $setSql WHERE $whereSql";

        $stmt = $this->getConnection()->prepare($query);

        $values = array_merge(array_values($set), array_values($where));

        return $stmt->execute($values);
    }

    public function delete(string $tableName, array $where): bool
    {
        UtilityClass::validateMapArray($where);
        $whereClause = Database::prepareSqlPairs(" AND ", array_keys($where));

        $sql = "DELETE FROM $tableName WHERE $whereClause";
        $stmt = $this->getConnection()->prepare($sql);
        return $stmt->execute(array_values($where));
    }

    public function exists(string $tableName, array $where): bool
    {
        return !empty($this->select($tableName, $where, '*', 1));
    }

    private static function prepareSqlPairs(string $separator, array $data): string
    {
        $parts = [];
        foreach ($data as $column) {
            $parts[] = "$column = ?";
        }
        return implode($separator, $parts);
    }

}
