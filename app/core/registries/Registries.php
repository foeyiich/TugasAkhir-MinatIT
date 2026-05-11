<?php

namespace TugasAkhir\core\registries;

use TugasAkhir\core\EnvironmentVariable;
use TugasAkhir\core\EnvKey;
use TugasAkhir\core\registries\keys\CookieKey;
use TugasAkhir\core\registries\keys\SessionKey;

final class Registries
{
    public static function getEnv(EnvKey $key): string
    {
        return EnvironmentVariable::get($key);
    }

    public static function setSession(SessionKey $sessionKey, string $value = ''): void
    {
        $_SESSION[$sessionKey->name] = $value;
    }

    public static function getSession(SessionKey $key): string
    {
        return $_SESSION[$key->name];
    }

    public static function setCookie(CookieKey $key, string $value = '', int $time = 3600): bool
    {
        setcookie($key->name, $value, time() + $time);
    }

    public static function getCookie(CookieKey $key): string
    {
        return $_COOKIE[$key->name];
    }
}
