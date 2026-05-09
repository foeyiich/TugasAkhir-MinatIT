<?php

namespace TugasAkhir\models\users;

use InvalidArgumentException;
use TugasAkhir\core\Database;
use TugasAkhir\core\Registries;
use TugasAkhir\models\DataModel;
use TugasAkhir\models\roles\Permission;
use TugasAkhir\models\roles\Role;
use UnexpectedValueException;

class User extends DataModel
{

    protected static function getDatabase(): ?Database
    {
        return Registries::get("mainDB");
    }

    protected static function getTableName(): string
    {
        return "users";
    }

    protected static function getSchema(): array
    {
        return [
            "id" => "INT AUTO_INCREMENT PRIMARY KEY",
            "email" => "VARCHAR(100) NOT NULL UNIQUE",
            "username" => "VARCHAR(50) NOT NULL UNIQUE",
            "password" => "VARCHAR(255) NOT NULL",
            "role_id" => "INT NOT NULL",
            "last_login" => "DATETIME",
            "created_at" => "DATETIME DEFAULT CURRENT_TIMESTAMP",
            "updated_at" => "DATETIME DEFAULT CURRENT_TIMESTAMP"
        ];
    }

    public string $password;
    private string $rawPassword;

    public function __construct(
        public string        $email,
        public string        $username,
        string               $rawPassword,
        public Role|int      $role,
        bool                 $hash_password = true,
        public readonly ?int $id = null
    )
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address");
        }

        $this->rawPassword = $rawPassword;

        if (is_int($role)) {
            $foundRole = Role::findById($role);
            if($foundRole === null) {
                throw new InvalidArgumentException("Role with id '$role' not found");
            }
            $this->role = $foundRole;
        }

        if ($hash_password) {
            $this->password = password_hash($rawPassword, PASSWORD_DEFAULT);
        } else {
            $this->password = $rawPassword;
        }
    }

    public function register(): void
    {
        $id = $this->id;
        if ($this->id !== null && static::exists(['id' => $id])) {
            throw new InvalidArgumentException("User with id '$id' already exists");
        }

        $email = $this->email;
        $username = $this->username;
        $password = $this->password;
        $roleId = $this->role->id;

        if (!empty(static::select(['email' => $email], 'id', 1))) {
            throw new UnexpectedValueException("Email address is already registered.");
        }

        if (!empty(static::select(['username' => $username], 'id', 1))) {
            throw new UnexpectedValueException("Username is already used.");
        }

        static::insert([
            "email" => $email,
            "username" => $username,
            "password" => $password,
            "role_id" => $roleId
        ]);
    }

    public function login(): ?self
    {
        $userData = static::get(['email' => $this->email]);

        if ($userData && password_verify($this->rawPassword, $userData->password)) {
            $_SESSION['user_id'] = $userData->id;
            $_SESSION['username'] = $userData->username;
            $_SESSION['role_id'] = $userData->role->id;

            static::update(['last_login' => date("Y-m-d H:i:s")], ['id' => $userData->id]);
            return $userData;
        }

        return null;
    }

    public function hasPermission(Permission $permission): bool
    {
        return !empty(in_array($permission, $this->role->permissions));
    }

    public static function get(array $condition): ?static
    {
        $data = static::select($condition, '*', 1);
        if (empty($data)) {
            return null;
        }
        $u = $data[0];
        return new static($u['email'], $u['username'], $u['password'], $u['role_id'], false, (int)$u['id']);
    }

    public static function update(array $set, array $where): bool
    {
        $set['updated_at'] = date("Y-m-d H:i:s");
        return parent::update($set, $where);
    }

}
