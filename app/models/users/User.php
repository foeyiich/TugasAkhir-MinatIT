<?php

namespace TugasAkhir\models\users;

use InvalidArgumentException;
use TugasAkhir\core\Database;
use TugasAkhir\core\registries\keys\CookieKey;
use TugasAkhir\core\registries\keys\SessionKey;
use TugasAkhir\core\registries\Registries;
use TugasAkhir\models\DataModel;
use TugasAkhir\models\roles\Permission;
use TugasAkhir\models\roles\Role;
use UnexpectedValueException;

class User extends DataModel
{

    private const DATE_FORMAT = 'Y-m-d H:i:s';
    private const SECURITY_STAMP_LENGTH = 32;
    private const REMEMBER_ME_TIME = 60 * 60 * 24 * 10;

    protected static function getDatabase(): ?Database
    {
        return Registries::getMainDatabase();
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
            "updated_at" => "DATETIME DEFAULT CURRENT_TIMESTAMP",
            "token" => "VARCHAR(" . self::SECURITY_STAMP_LENGTH . ") NOT NULL",
        ];
    }

    public string $password;

    public function __construct(
        public string        $email,
        public string        $username,
        string               $rawPassword,
        public Role|int      $role,
        bool                 $hashPassword = true,
        public readonly ?int $id = null
    )
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address");
        }

        if (is_int($role)) {
            $foundRole = Role::findById($role);
            if ($foundRole === null) {
                throw new InvalidArgumentException("Role with id '$role' not found");
            }
            $this->role = $foundRole;
        }

        if ($this->role instanceof Role && $this->role->id === null) {
            throw new InvalidArgumentException("Role must have an id");
        }

        if ($hashPassword) {
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

        if (static::exists(['email' => $email])) {
            throw new UnexpectedValueException("Email address is already registered.");
        }

        if (static::exists(['username' => $username])) {
            throw new UnexpectedValueException("Username is already used.");
        }

        static::insert([
            "email" => $email,
            "username" => $username,
            "password" => $password,
            "role_id" => $roleId,
            "token" => static::generateToken()
        ]);
    }

    public function hasPermission(Permission $permission): bool
    {
        return !empty(in_array($permission, $this->role->permissions));
    }

    public static function findById(int $id): ?static
    {
        return static::get(['id' => $id]);
    }

    public static function get(array $condition): ?static
    {
        $data = static::select($condition, '*', 1);
        if (empty($data)) {
            return null;
        }

        return static::fromRow($data[0]);
    }

    public static function findByRole(Role|int $role, int $limit = 100): array
    {
        $roleId = $role instanceof Role ? $role->id : $role;

        if ($roleId === null) {
            throw new InvalidArgumentException("Role must have an id");
        }

        $rows = static::select(['role_id' => $roleId], '*', $limit);

        return array_map(fn(array $row) => static::fromRow($row), $rows);
    }

    public static function allAccounts(int $limit = 100): array
    {
        $rows = static::selectAll($limit);

        return array_map(fn(array $row) => static::fromRow($row), $rows);
    }

    public static function createAccount(CreateAccountData $data): static
    {
        $user = new static($data->email, $data->username, $data->password, $data->role);
        $user->register();

        $createdUser = static::get(['email' => $data->email]);

        if ($createdUser === null) {
            throw new UnexpectedValueException("Failed to create user account.");
        }

        return $createdUser;
    }

    public static function updateAccount(int $id, UpdateAccountData $data): bool
    {
        $set = [];

        if ($data->email !== null) {
            $set['email'] = $data->email;
        }

        if ($data->username !== null) {
            $set['username'] = $data->username;
        }

        if ($data->role !== null) {
            $roleId = $data->role instanceof Role ? $data->role->id : $data->role;

            if ($roleId === null || Role::findById((int)$roleId) === null) {
                throw new InvalidArgumentException("Role not found.");
            }

            $set['role_id'] = (int)$roleId;
        }

        if (!$data->hasChanges() || empty($set)) {
            return false;
        }

        return static::update($set, ['id' => $id]);
    }

    public static function changePassword(int $id, string $newPassword): bool
    {
        return static::update(
            ['password' => password_hash($newPassword, PASSWORD_DEFAULT)],
            ['id' => $id]
        );
    }

    public static function deleteAccount(int $id): bool
    {
        return static::delete(['id' => $id]);
    }

    public static function update(array $set, array $where): bool
    {
        $set['updated_at'] = date(self::DATE_FORMAT);
        if (array_key_exists('email', $set) || array_key_exists('password', $set)) {
            $set['token'] = static::generateToken();
        }
        return parent::update($set, $where);
    }

    public static function authenticate(string $email, string $rawPassword, bool $rememberMe = false): ?self
    {

        $userData = static::get(['token' => Registries::getCookie(CookieKey::USER_TOKEN)]);
        if (is_null($userData)) {
            $userData = static::get(['email' => $email]);
        }


        if ($userData && password_verify($rawPassword, $userData->password)) {
            session_regenerate_id(true);

            Registries::setSession(SessionKey::USER_ID, $userData->id);
            Registries::setSession(SessionKey::USER_EMAIL, $userData->email);
            Registries::setSession(SessionKey::USER_USERNAME, $userData->username);
            Registries::setSession(SessionKey::USER_ROLE, $userData->role->id);

            if ($rememberMe) {
                $tokenRows = static::select(["email" => $email], ["token"], 1);
                $token = $tokenRows[0]['token'] ?? null;

                if ($token !== null) {
                    Registries::setCookie(
                        CookieKey::USER_TOKEN,
                        $token,
                        self::REMEMBER_ME_TIME
                    );
                }
            }

            static::update(['last_login' => date(self::DATE_FORMAT)], ['id' => $userData->id]);

            return $userData;
        }

        return null;
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(self::SECURITY_STAMP_LENGTH / 2));
    }

    private static function fromRow(array $row): static
    {
        return new static(
            $row['email'],
            $row['username'],
            $row['password'],
            (int)$row['role_id'],
            false,
            (int)$row['id']
        );
    }

}
