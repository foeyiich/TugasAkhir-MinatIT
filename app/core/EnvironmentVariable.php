<?php

namespace TugasAkhir\core;

/**
 * Class EnvironmentVariable.
 * Environment configuration. Auto generates a default .env file if missing,
 * but safely halts execution until the developer
 * explicitly change the configuration.
 */
final class EnvironmentVariable
{

    private const string FILE_PATH = PROJECT_ROOT . DIRECTORY_SEPARATOR . '.env';

    private const array DEFAULTS = [
        '# Change this to false or remove this line once you have configured the file properly.',
        EnvKey::JUST_GENERATED->name => 'true',
        '',
        EnvKey::DEBUG_MODE->name => 'true',
        '',
        '# Database Configuration',
        EnvKey::DB_TYPE->name => 'sqlite',
        '',
        '#  Points to the SQLite file. Ignore this if you are using MySQL.',
        EnvKey::DB_SQLITE_FILE->name => 'database.sqlite',
        '',
        '#  Pointing to the MySQL server. Ignore this if you are using SQLite',
        EnvKey::DB_MYSQL_HOST->name => 'localhost',
        EnvKey::DB_MYSQL_USER->name => 'root',
        EnvKey::DB_MYSQL_PASSWORD->name => '',
        EnvKey::DB_MYSQL_PORT->name => '3306',
        EnvKey::DB_MYSQL_DATABASE->name => 'tugas_akhir',
    ];

    private static array $env = [];

    private static function generateDefaultFile(): void
    {
        if (file_exists(self::FILE_PATH)) {
            return;
        }
        $content = "";
        foreach (self::DEFAULTS as $key => $value) {
            if (is_int($key)) {
                $content .= $value . PHP_EOL;
                continue;
            }
            $content .= "{$key}={$value}" . PHP_EOL;
        }
        if (file_put_contents(self::FILE_PATH, $content) === false) {
            die("Failed to write to .env file. Please check your directory permissions.");
        }
    }

    public static function load(): void
    {
        if (!file_exists(self::FILE_PATH)) {
            self::generateDefaultFile();
            echo "<pre>";
            echo "============= [ CONFIGURATION REQUIRED ] =============" . PHP_EOL;
            echo "A new env file has been automatically generated." . PHP_EOL;
            echo "Please open the file, configure your settings, and set" . PHP_EOL;
            echo "JUST_GENERATED to `false` to start the application." . PHP_EOL;
            echo "======================================================" . PHP_EOL;
            echo "</pre>";
            die();
        }

        $data = parse_ini_file(self::FILE_PATH);
        self::$env = $data ?: [];

        if (self::get(EnvKey::JUST_GENERATED) === true) {
            echo "<pre>";
            echo "============= [ CONFIGURATION REQUIRED ] =============" . PHP_EOL;
            echo "The env file JUST_GENERATED still in true." . PHP_EOL;
            echo "Change to `false` to start the application." . PHP_EOL;
            echo "======================================================" . PHP_EOL;
            echo "</pre>";
            die();
        }
    }

    /**
     * Retrieves the value of a specific environment variable.
     * @param EnvKey $envKey
     * @return mixed The value from the environment file, or the default value if not found.
     */
    public static function get(EnvKey $envKey): mixed
    {
        $rawValue = self::$env[$envKey->name] ?? self::getDefault($envKey);
        if ($rawValue === null) {
            return null;
        }
        return filter_var($rawValue, $envKey->type());
    }

    /**
     * @param EnvKey $envKey
     * @return string|null The default value, or null if no default is defined.
     */
    public static function getDefault(EnvKey $envKey): mixed
    {
        return self::DEFAULTS[$envKey->name] ?? null;
    }

}
