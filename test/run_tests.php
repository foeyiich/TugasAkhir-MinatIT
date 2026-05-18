<?php
declare(strict_types=1);
ob_start();

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.php';

use Test\core\data\DatabaseTest;
use Test\model\RoleTest;
use Test\model\UserTest;

if (PHP_SAPI !== 'cli') {
    header('HTTP/1.1 403 Forbidden');
    die("Error: Testing environment is only accessible via CLI (Terminal).\n");
}

echo "\e[1;35m================================================================================\n";
echo "                         STARTING AUTOMATED TEST RUNNER                         \n";
echo "================================================================================\n\e[0m\n";

$testSuites = [
    new DatabaseTest(),
    new RoleTest(),
    new UserTest(),
];

foreach ($testSuites as $suite) {
    $suite->printStart();

    try {
        $suite->onStart();
        $suite->run();
    } catch (Throwable $e) {
        echo "\e[1;31m⚠️  FATAL ERROR [" . $suite::class . "]: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n\e[0m";
    } finally {
        $suite->onStop();
        $suite->printSummary();
    }
}

echo "\e[1;35m================================================================================\n";
echo "                           ALL TEST SUITES COMPLETED                            \n";
echo "================================================================================\n\e[0m\n";
