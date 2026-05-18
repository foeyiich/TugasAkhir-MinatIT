<?php

namespace TugasAkhir\core\registry;

use TugasAkhir\core\registry\key\CookieKey;
use TugasAkhir\SingletonClass;

/**
 * Class CookieManager
 * * Provides a secure, type-safe singleton interface for managing HTTP cookies.
 * Uses the CookieKey enum to prevent magic strings and implement
 */
final class CookieManager extends SingletonClass
{
    /**
     * @param CookieKey $key
     * @param string $value The value to be stored in the cookie. Defaults to an empty string.
     * @param int $time The expiration time in seconds from the current time. Default = 3600 (1 hour).
     * @param bool $httpOnly Whether to restrict cookie access to the HTTP protocol only (mitigates XSS). Default = true.
     * @return bool True if the cookie was successfully dispatched to the client, false otherwise.
     */
    public function set(CookieKey $key, string $value = '', int $time = 3600, bool $httpOnly = true): bool
    {
        $options = [
            'expires' => time() + $time,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => $httpOnly,
        ];

        return setcookie($key->name, $value, $options);
    }

    /**
     * @param CookieKey $key
     * @param mixed $default The default value to return if the cookie does not exist.
     * @return mixed The cookie value, or the default value if not found.
     */
    public function get(CookieKey $key, mixed $default = null): mixed
    {
        return $_COOKIE[$key->name] ?? $default;
    }

    /**
     * @param CookieKey $key
     * @return bool True if the cookie deletion header was successfully dispatched.
     */
    public function remove(CookieKey $key): bool
    {
        if (isset($_COOKIE[$key->name])) {
            unset($_COOKIE[$key->name]);
        }

        $options = [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        return setcookie($key->name, '', $options);
    }
}