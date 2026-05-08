<?php

class Database
{

    private static ?self $instance = null;
    private ?PDO $connection = null;

    private function __construct()
    {
        $type = EnvironmentVariable::get(EnvKey::DB_TYPE);

        try {
            if ($type === 'sql') {
                $dbFile = EnvironmentVariable::get(EnvKey::DB_SQL_FILE);
                $dataSourceName = "sqlite:" . PROJECT_DIR . "/" . $dbFile;
                $this->connection = new PDO($dataSourceName);
            } elseif ($type === "mysql") {
                $host = EnvironmentVariable::get(EnvKey::DB_MYSQL_HOST);
                $name = EnvironmentVariable::get(EnvKey::DB_MYSQL_NAME);
                $user = EnvironmentVariable::get(EnvKey::DB_MYSQL_USER);
                $pass = EnvironmentVariable::get(EnvKey::DB_MYSQL_PASS);

                $dataSourceName = "mysql:host=$host;dbname=$name;charset=utf8mb4";
                $this->connection = new PDO($dataSourceName, $user, $pass);
            }

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection Error: " . $e->getMessage());
        }
    }

    public static function getConnection(): PDO
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }

}
