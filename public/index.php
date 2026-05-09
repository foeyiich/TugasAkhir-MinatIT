<?php

use TugasAkhir\core\Database;
use TugasAkhir\core\EnvironmentVariable;
use TugasAkhir\core\EnvKey;
use TugasAkhir\core\Registries;
use TugasAkhir\models\roles\Role;
use TugasAkhir\models\users\User;

define('PROJECT_ROOT', dirname(__DIR__));
spl_autoload_register(function ($className) {
    $prefix = 'TugasAkhir\\';

    $baseDir = PROJECT_ROOT . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;

    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) !== 0) {
        return;
    }

    $relativeClass = substr($className, $len);

    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
session_start();

EnvironmentVariable::load();

$mainDatabase = null;
if (EnvironmentVariable::get(EnvKey::DB_TYPE) == "sqlite") {
    $mainDatabase = Database::createSqlite(EnvironmentVariable::get(EnvKey::DB_SQL_FILE));
} elseif (EnvironmentVariable::get(EnvKey::DB_TYPE) == "mysql") {
    $mainDatabase = Database::createMySql(
        EnvironmentVariable::get(EnvKey::DB_MYSQL_HOST),
        EnvironmentVariable::get(EnvKey::DB_MYSQL_NAME),
        EnvironmentVariable::get(EnvKey::DB_MYSQL_USER),
        EnvironmentVariable::get(EnvKey::DB_MYSQL_PASS)
    );
} else {
    die("Database type not supported");
}
Registries::bind("mainDB", $mainDatabase);

Role::init();
User::init();
