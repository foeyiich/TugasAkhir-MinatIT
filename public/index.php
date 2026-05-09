<?php
session_start();

define('PROJECT_ROOT', dirname(__DIR__));

spl_autoload_register(function ($className) {
    $directories = [
        'config/',
        'database/',
        'app/models/users/',
        'app/models/'
    ];

    foreach ($directories as $directory) {
        $file = PROJECT_ROOT . DIRECTORY_SEPARATOR . $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

EnvironmentVariable::load();
$database = Database::getConnection();

DataModels::setConnection($database);
Role::init();
User::init();
