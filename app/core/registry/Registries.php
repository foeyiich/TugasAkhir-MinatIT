<?php

namespace TugasAkhir\core\registry;

use TugasAkhir\core\data\Database;
use TugasAkhir\core\EnvironmentVariable;
use TugasAkhir\core\EnvKey;

final class Registries
{

    private function __construct()
    {
        // Utility Class
    }

    private static ?Database $mainDatabase = null;

    public static function setMainDatabase(Database $database): void
    {
        self::$mainDatabase = $database;
    }

    public static function getMainDatabase(): ?Database
    {
        return self::$mainDatabase;
    }

    public static function env(EnvKey $key): mixed
    {
        return EnvironmentVariable::get($key);
    }

    public static function session(): SessionManager
    {
        return SessionManager::getInstance();
    }

    public static function cookie(): CookieManager
    {
        return CookieManager::getInstance();
    }

}
