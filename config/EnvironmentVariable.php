<?php

class EnvironmentVariable
{
    public const FILE_PATH = PROJECT_ROOT . DIRECTORY_SEPARATOR . ".env";

    private static array $env = [];

    private static array $defaults = [
        '# Database Configuration',
        EnvKey::DB_TYPE->name => 'sql',
        '',
        '#  Points to the SQLite file. Ignore this if you are using MySQL.',
        EnvKey::DB_SQL_FILE->name => 'database.sqlite',
        '',
        '#  Pointing to the MySQL server. Ignore this if you are using SQL',
        EnvKey::DB_MYSQL_HOST->name => 'localhost',
        EnvKey::DB_MYSQL_NAME->name => 'tugas_akhir',
        EnvKey::DB_MYSQL_USER->name => 'root',
        EnvKey::DB_MYSQL_PASS->name => ''
    ];

    public static function load(): void
    {
        if (!file_exists(self::FILE_PATH)) {
            self::initializeFile();
        }

        $envData = parse_ini_file(self::FILE_PATH) ?: [];

        self::$env = array_merge(self::$defaults, $envData);
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
        return self::$env[$envKey->name] ?? null;
    }

}
