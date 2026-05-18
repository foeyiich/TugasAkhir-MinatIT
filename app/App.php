<?php

namespace TugasAkhir;

use TugasAkhir\core\data\Database;
use TugasAkhir\core\EnvironmentVariable;
use TugasAkhir\core\EnvKey;
use TugasAkhir\core\registry\Registries;
use TugasAkhir\model\role\Role;
use TugasAkhir\model\user\User;

final class App extends SingletonClass
{

    protected function __construct()
    {
        parent::__construct();

        Registries::session()->start();

        EnvironmentVariable::load();

        if (!Registries::env(EnvKey::DEBUG_MODE)) {
            error_reporting(0);
        }

        if (Registries::env(EnvKey::DB_TYPE) === "sqlite") {
            $mainDatabase = Database::createSqlite(
                Registries::env(EnvKey::DB_SQLITE_FILE)
            );
        } elseif (Registries::env(EnvKey::DB_TYPE) === "mysql") {
            $mainDatabase = Database::createMySql(
                Registries::env(EnvKey::DB_MYSQL_HOST),
                Registries::env(EnvKey::DB_MYSQL_DATABASE),
                Registries::env(EnvKey::DB_MYSQL_USER),
                Registries::env(EnvKey::DB_MYSQL_PASSWORD)
            );
        } else {
            die("Database type not supported");
        }

        Registries::setMainDatabase($mainDatabase);

        Role::init();
        Role::seedDefaults();

        User::init();
    }
}
