<?php

namespace TugasAkhir\core;

use TugasAkhir\core\registries\keys\CookieKey;
use TugasAkhir\core\registries\keys\SessionKey;
use TugasAkhir\core\registries\Registries;
use TugasAkhir\models\roles\Permission;
use TugasAkhir\models\users\User;

final class Auth
{
    private function __construct()
    {
    }

    public static function attempt(string $email, string $password, bool $rememberMe = false): ?User
    {
        return User::authenticate($email, $password, $rememberMe);
    }

    public static function check(): bool
    {
        return Registries::getSession(SessionKey::USER_ID) !== null;
    }

    public static function id(): ?int
    {
        $id = Registries::getSession(SessionKey::USER_ID);

        return $id === null ? null : (int)$id;
    }

    public static function user(): ?User
    {
        $id = self::id();

        if ($id === null) {
            return null;
        }

        return User::get(['id' => $id]);
    }

    public static function roleId(): ?int
    {
        $roleId = Registries::getSession(SessionKey::USER_ROLE);

        return $roleId === null ? null : (int)$roleId;
    }

    public static function hasPermission(Permission $permission): bool
    {
        $user = self::user();

        return $user !== null && $user->hasPermission($permission);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: index.php');
            exit;
        }
    }

    public static function requirePermission(Permission $permission): void
    {
        self::requireLogin();

        if (!self::hasPermission($permission)) {
            http_response_code(403);
            die('403 Forbidden');
        }
    }

    public static function logout(): void
    {
        Registries::removeSession(SessionKey::USER_ID);
        Registries::removeSession(SessionKey::USER_EMAIL);
        Registries::removeSession(SessionKey::USER_USERNAME);
        Registries::removeSession(SessionKey::USER_ROLE);
        Registries::removeCookie(CookieKey::USER_TOKEN);

        session_regenerate_id(true);
    }

    public static function updatePassword(string $currentPassword, string $newPassword): bool
    {
        $user = self::user();

        if ($user === null) {
            return false;
        }

        if (!password_verify($currentPassword, $user->password)) {
            return false;
        }

        return User::update(
            ['password' => password_hash($newPassword, PASSWORD_DEFAULT)],
            ['id' => $user->id]
        );
    }
}
