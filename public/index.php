<?php
declare(strict_types=1);

require_once "../bootstrap/bootstrap.php";

use TugasAkhir\App;
use TugasAkhir\core\Authentication;

App::getInstance();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);

    $user = Authentication::attempt($email, $password, $rememberMe);

    if ($user !== null) {
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Email atau password salah.';
}

require_once PROJECT_ROOT . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'login.php';

