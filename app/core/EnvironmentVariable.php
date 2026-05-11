<?php

namespace TugasAkhir\core;

class EnvironmentVariable
{
    public const string FILE_PATH = PROJECT_ROOT . DIRECTORY_SEPARATOR . ".env";

    private static array $env = [];

    private static array $defaults = [
        '# Database Configuration',
        EnvKey::DB_TYPE->name => 'sqlite',
        '',
        '#  Points to the SQLite file. Ignore this if you are using MySQL.',
        EnvKey::DB_SQLITE_FILE->name => 'database.sqlite',
        '',
        '#  Pointing to the MySQL server. Ignore this if you are using SQLite',
        "#" . EnvKey::DB_MYSQL_HOST->name => 'localhost',
        "#" . EnvKey::DB_MYSQL_USER->name => 'root',
        "#" . EnvKey::DB_MYSQL_PASSWORD->name => '',
        "#" . EnvKey::DB_MYSQL_PORT->name => '3306',
        "#" . EnvKey::DB_MYSQL_DATABASE->name => 'tugas_akhir',
    ];

    public static function load(): void
    {
        if (!file_exists(self::FILE_PATH)) {
            self::initializeFile();
        }

        $data = parse_ini_file(self::FILE_PATH);
        self::$env = $data ?: [];
    }

    private static function initializeFile(): void
    {
        $content = "";
        foreach (self::$defaults as $key => $value) {
            if (is_int($key)) {
                $content .= $value . PHP_EOL;
                continue;
            }
            $content .= "{$key}={$value}" . PHP_EOL;
        }
        file_put_contents(self::FILE_PATH, $content);
    }

    public static function get(EnvKey $envKey): ?string
    {
        return self::$env[$envKey->name] ?? self::getDefault($envKey) ?? '';
    }

    public static function getDefault(EnvKey $envKey): ?string
    {
        return self::$defaults[$envKey->name] ?? null;
    }

}
