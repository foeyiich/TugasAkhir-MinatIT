<?php

namespace TugasAkhir\controllers;

use TugasAkhir\core\Authentication;
use TugasAkhir\model\role\Permission;
use TugasAkhir\model\user\CreateAccountData;
use TugasAkhir\model\user\UpdateAccountData;
use TugasAkhir\model\user\User;

final class AccountController
{
    private function __construct()
    {
    }

    public static function index(int $limit = 100): array
    {
        Authentication::requirePermission(Permission::MANAGE_ACCOUNTS);

        return User::allAccounts($limit);
    }

    public static function show(int $id): ?User
    {
        Authentication::requirePermission(Permission::MANAGE_ACCOUNTS);

        return User::findById($id);
    }

    public static function store(CreateAccountData $data): User
    {
        Authentication::requirePermission(Permission::MANAGE_ACCOUNTS);

        return User::createAccount($data);
    }

    public static function update(int $id, UpdateAccountData $data): bool
    {
        Authentication::requirePermission(Permission::MANAGE_ACCOUNTS);

        return User::updateAccount($id, $data);
    }

    public static function changePassword(int $id, string $newPassword): bool
    {
        Authentication::requirePermission(Permission::MANAGE_ACCOUNTS);

        return User::changePassword($id, $newPassword);
    }

    public static function destroy(int $id): bool
    {
        Authentication::requirePermission(Permission::MANAGE_ACCOUNTS);

        return User::deleteAccount($id);
    }
}
