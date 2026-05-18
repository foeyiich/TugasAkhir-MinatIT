<?php

require_once "../vendor/autoload.php";

define("PROJECT_ROOT", dirname(__DIR__));

if (PHP_VERSION !== "8.5.6") {
    die("<h1>PHP version " . PHP_VERSION . " is not supported.</h1><br><h3>Please use 8.5.6</h3>");
}