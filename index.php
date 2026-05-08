<?php

const PROJECT_DIR = __DIR__;
spl_autoload_register(function ($className) {
    $directories = [
        'config/',
        'database/',
        'app/Models/'
    ];

    foreach ($directories as $directory) {
        $file = PROJECT_DIR . '/' . $directory . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});


EnvironmentVariable::load();
$database = Database::getConnection();

DataModel::setConnection($database);
User::init();

try {
    new User("email@gmail.com", "foeyii", "123", 1);
} catch (\PDOException $e) {
    echo $e->getMessage();
}
