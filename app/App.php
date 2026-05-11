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

    private static ?self $instance;

    public readonly Database $mainDatabase;

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        EnvironmentVariable::load();
        Role::init();
        Role::seedDefaults();
        User::init();

        if (Registries::getEnv(EnvKey::DB_TYPE) == "sqlite") {
            $this->mainDatabase = Database::createSqlite(Registries::getEnv(EnvKey::DB_SQLITE_FILE));
        } elseif (Registries::getEnv(EnvKey::DB_TYPE) == "mysql") {
            $this->mainDatabase = Database::createMysql(
                Registries::getEnv(EnvKey::DB_MYSQL_HOST),
                Registries::getEnv(EnvKey::DB_MYSQL_DATABASE),
                Registries::getEnv(EnvKey::DB_MYSQL_USER),
                Registries::getEnv(EnvKey::DB_MYSQL_PASSWORD)
            );
        }
    }

}
