<?php

namespace TugasAkhir\core\registry;

use RuntimeException;
use TugasAkhir\core\registry\key\SessionKey;
use TugasAkhir\SingletonClass;

final class SessionManager extends SingletonClass
{

    /**
     * @return void
     * @throws RuntimeException If headers have already been sent.
     */
    public function start(): bool
    {
        if (session_status() === PHP_SESSION_NONE && headers_sent($file, $line)) {
            throw new RuntimeException("Cannot start session: Headers already sent in $file on line $line.");
        }
        return session_start([
            'cookie_httponly' => true,
            'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'cookie_samesite' => 'Lax',
            'use_strict_mode' => true
        ]);
    }

    /**
     * @param bool $deleteOldSession Whether to delete the old session data file on the server. Defaults to true.
     * @return bool True on success, false on failure.
     */
    public function regenerateId(bool $deleteOldSession = true): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return session_regenerate_id($deleteOldSession);
        }
        return false;
    }

    /**
     * @param SessionKey $sessionKey
     * @param mixed $value The value to store in the session. Defaults to an empty string.
     * @return void
     */
    public function set(SessionKey $sessionKey, mixed $value = ''): void
    {
        $this->ensureSessionActive();
        $_SESSION[$sessionKey->name] = $value;
    }

    /**
     * Retrieves a session value safely.
     *
     * @param SessionKey $key
     * @param mixed $default The default value to return if the session key does not exist.
     * @return mixed The session value, or the default value if not found.
     */
    public function get(SessionKey $key, mixed $default = null): mixed
    {
        $this->ensureSessionActive();
        return $_SESSION[$key->name] ?? $default;
    }

    /**
     * Removes a specific key from the session.
     *
     * @param SessionKey $key The strongly-typed enum representing the session key.
     * @return void
     */
    public function remove(SessionKey $key): void
    {
        $this->ensureSessionActive();
        unset($_SESSION[$key->name]);
    }

    /**
     * Clears the memory, deletes the session file on the server, and removes the session cookie.\
     *
     * @return void
     */
    public function destroy(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];

            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
            }

        }
        return session_destroy();
    }

    /**
     * @return void
     */
    private function ensureSessionActive(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $this->start();
        }
    }
}