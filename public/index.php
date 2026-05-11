<?php
use TugasAkhir\models\users\User;
use TugasAkhir\App;

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

App::getInstance();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);

    $user = User::authenticate($email, $password, $rememberMe);

    if ($user !== null) {
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Email atau password salah.';
}

require PROJECT_ROOT . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'login.php';
