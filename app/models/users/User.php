<?php

namespace TugasAkhir\models\users;

use InvalidArgumentException;
use TugasAkhir\App;
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

    private const string DATE_FORMAT = 'Y-m-d H:i:s';
    private const int SECURITY_STAMP_LENGTH = 32;
    private const int REMEMBER_ME_TIME = 60 * 60 * 24 * 10;

    protected static function getDatabase(): ?Database
    {
        return App::getInstance()->mainDatabase;
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

        $this->password = password_hash($rawPassword, PASSWORD_DEFAULT);
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

    public static function get(array $condition): ?static
    {
        $data = static::select($condition, '*', 1);
        if (empty($data)) {
            return null;
        }
        $u = $data[0];
        return new static($u['email'], $u['username'], $u['password'], $u['role_id'], (int)$u['id']);
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
            Registries::setSession(SessionKey::USER_ID, $userData->id);
            Registries::setSession(SessionKey::USER_EMAIL, $userData->email);
            Registries::setSession(SessionKey::USER_USERNAME, $userData->username);
            Registries::setSession(SessionKey::USER_ROLE, $userData->role);

            if ($rememberMe) {
                Registries::setCookie(
                    CookieKey::USER_TOKEN,
                    static::select(["email" => $email], ["token"])["token"],
                    self::REMEMBER_ME_TIME,
                );
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

}
