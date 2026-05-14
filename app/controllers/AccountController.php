<?php

namespace TugasAkhir\controllers;

use TugasAkhir\core\Auth;
use TugasAkhir\models\roles\Permission;
use TugasAkhir\models\users\CreateAccountData;
use TugasAkhir\models\users\UpdateAccountData;
use TugasAkhir\models\users\User;

final class AccountController
{
    private function __construct()
    {
    }

    public static function index(int $limit = 100): array
    {
        Auth::requirePermission(Permission::MANAGE_ACCOUNTS);

        return User::allAccounts($limit);
    }

    public static function show(int $id): ?User
    {
        Auth::requirePermission(Permission::MANAGE_ACCOUNTS);

        return User::findById($id);
    }

    public static function store(CreateAccountData $data): User
    {
        Auth::requirePermission(Permission::MANAGE_ACCOUNTS);

        return User::createAccount($data);
    }

    public static function update(int $id, UpdateAccountData $data): bool
    {
        Auth::requirePermission(Permission::MANAGE_ACCOUNTS);

        return User::updateAccount($id, $data);
    }

    public static function changePassword(int $id, string $newPassword): bool
    {
        Auth::requirePermission(Permission::MANAGE_ACCOUNTS);

        return User::changePassword($id, $newPassword);
    }

    public static function destroy(int $id): bool
    {
        Auth::requirePermission(Permission::MANAGE_ACCOUNTS);

        return User::deleteAccount($id);
    }
}
