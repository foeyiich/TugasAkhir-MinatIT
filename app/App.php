<?php

namespace TugasAkhir;

use TugasAkhir\core\Database;
use TugasAkhir\core\EnvironmentVariable;
use TugasAkhir\core\EnvKey;
use TugasAkhir\core\registries\Registries;
use TugasAkhir\models\roles\Role;
use TugasAkhir\models\users\User;

final class App
{
    private static ?self $instance = null;

    public readonly Database $mainDatabase;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        EnvironmentVariable::load();

        if (Registries::getEnv(EnvKey::DB_TYPE) === "sqlite") {
            $this->mainDatabase = Database::createSqlite(
                Registries::getEnv(EnvKey::DB_SQLITE_FILE)
            );
        } elseif (Registries::getEnv(EnvKey::DB_TYPE) === "mysql") {
            $this->mainDatabase = Database::createMySql(
                Registries::getEnv(EnvKey::DB_MYSQL_HOST),
                Registries::getEnv(EnvKey::DB_MYSQL_DATABASE),
                Registries::getEnv(EnvKey::DB_MYSQL_USER),
                Registries::getEnv(EnvKey::DB_MYSQL_PASSWORD)
            );
        } else {
            die("Database type not supported");
        }

        Registries::bind("mainDB", $this->mainDatabase);

        Role::init();
        Role::seedDefaults();

        User::init();
    }
}
