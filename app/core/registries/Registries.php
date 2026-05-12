<?php

namespace TugasAkhir\core\registries;

use TugasAkhir\core\Database;
use TugasAkhir\core\EnvironmentVariable;
use TugasAkhir\core\EnvKey;
use TugasAkhir\core\registries\keys\CookieKey;
use TugasAkhir\core\registries\keys\SessionKey;

final class Registries
{
    private static ?Database $mainDatabase = null;

    public static function setMainDatabase(Database $database): void
    {
        self::$mainDatabase = $database;
    }

    public static function getMainDatabase(): ?Database
    {
        return self::$mainDatabase;
    }

    public static function getEnv(EnvKey $key): string
    {
        return EnvironmentVariable::get($key);
    }

    public static function setSession(SessionKey $sessionKey, mixed $value = ''): void
    {
        $_SESSION[$sessionKey->name] = $value;
    }

    public static function getSession(SessionKey $key, mixed $default = null): mixed
    {
        return $_SESSION[$key->name] ?? $default;
    }

    public static function removeSession(SessionKey $key): void
    {
        unset($_SESSION[$key->name]);
    }

    public static function setCookie(CookieKey $key, string $value = '', int $time = 3600): bool
    {
        return setcookie($key->name, $value, time() + $time, "/");
    }

    public static function getCookie(CookieKey $key, mixed $default = null): mixed
    {
        return $_COOKIE[$key->name] ?? $default;
    }

    public static function removeCookie(CookieKey $key): bool
    {
        unset($_COOKIE[$key->name]);

        return setcookie($key->name, '', time() - 3600, "/");
    }
}
