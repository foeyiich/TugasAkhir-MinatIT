<?php


namespace TugasAkhir\model\user;

use InvalidArgumentException;
use Random\RandomException;
use TugasAkhir\core\data\Database;
use TugasAkhir\core\registry\key\CookieKey;
use TugasAkhir\core\registry\key\SessionKey;
use TugasAkhir\core\registry\Registries;
use TugasAkhir\model\DataModel;
use TugasAkhir\model\role\Permission;
use TugasAkhir\model\role\Role;
use UnexpectedValueException;

class User extends DataModel
{

    private const string DATE_FORMAT = 'Y-m-d H:i:s';
    private const int SECURITY_STAMP_LENGTH = 32;
    private const int REMEMBER_ME_TIME = (60 * 60 * 24) * 10;

    protected static function getDatabase(): Database
    {
        return Registries::getMainDatabase();
    }

    protected static function getTableName(): string
    {
        return "users";
    }

    protected static function getSchema(): array
    {
        return UserField::buildSchema();
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
        if ($this->id !== null && static::exists([UserField::ID->field() => $id])) {
            throw new InvalidArgumentException("User with id '$id' already exists");
        }

        $email = $this->email;
        $username = $this->username;
        $password = $this->password;
        $roleId = $this->role->id;

        if (static::exists([UserField::EMAIL->field() => $email])) {
            throw new UnexpectedValueException("Email address is already registered.");
        }

        if (static::exists([UserField::USERNAME->field() => $username])) {
            throw new UnexpectedValueException("Username is already used.");
        }

        static::insert([
            UserField::EMAIL->field() => $email,
            UserField::USERNAME->field() => $username,
            UserField::PASSWORD->field() => $password,
            UserField::ROLE_ID->field() => $roleId,
            UserField::TOKEN->field() => static::generateToken()
        ]);
    }

    public function hasPermission(Permission $permission): bool
    {
        return isset($this->role->permissions[$permission->name]);
    }

    public static function findById(int $id): ?static
    {
        return static::get([UserField::ID->field() => $id]);
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

        $rows = static::select([UserField::ROLE_ID->field() => $roleId], '*', $limit);

        return array_map(static fn(array $row) => static::fromRow($row), $rows);
    }

    public static function allAccounts(int $limit = 100): array
    {
        $rows = static::selectAll($limit);

        return array_map(static fn(array $row) => static::fromRow($row), $rows);
    }

    public static function createAccount(CreateAccountData $data): static
    {
        $user = new static($data->email, $data->username, $data->password, $data->role);
        $user->register();

        $createdUser = static::get([UserField::EMAIL->field() => $data->email]);

        if ($createdUser === null) {
            throw new UnexpectedValueException("Failed to create user account.");
        }

        return $createdUser;
    }

    public static function updateAccount(int $id, UpdateAccountData $data): bool
    {
        $set = [];

        if ($data->email !== null) {
            $set[UserField::EMAIL->field()] = $data->email;
        }

        if ($data->username !== null) {
            $set[UserField::USERNAME->field()] = $data->username;
        }

        if ($data->role !== null) {
            $roleId = $data->role instanceof Role ? $data->role->id : $data->role;

            if ($roleId === null || Role::findById((int)$roleId) === null) {
                throw new InvalidArgumentException("Role not found.");
            }

            $set[UserField::ROLE_ID->field()] = (int)$roleId;
        }

        if (!empty($set) && !$data->hasChanges()) {
            return false;
        }

        return static::update($set, [UserField::ID->field() => $id]);
    }

    public static function changePassword(int $id, string $newPassword): bool
    {
        return static::update(
            [UserField::PASSWORD->field() => password_hash($newPassword, PASSWORD_DEFAULT)],
            [UserField::ID->field() => $id]
        );
    }

    public static function deleteAccount(int $id): bool
    {
        return static::delete([UserField::ID->field() => $id]);
    }

    public static function update(array $set, array $where): bool
    {
        $set[UserField::UPDATED_AT->field()] = date(self::DATE_FORMAT);
        if (array_key_exists(UserField::EMAIL->field(), $set) || array_key_exists(UserField::PASSWORD->field(), $set)) {
            $set[UserField::TOKEN->field()] = static::generateToken();
        }
        return parent::update($set, $where);
    }


    public static function authenticate(string $email, string $rawPassword, bool $rememberMe = false): ?self
    {

        $userData = static::get([UserField::TOKEN->field() => Registries::cookie()->get(CookieKey::USER_TOKEN)]);
        if ($userData === null) {
            $userData = static::get([UserField::EMAIL->field() => $email]);
        }

        if ($userData && password_verify($rawPassword, $userData->password)) {
            Registries::session()->regenerateId();

            Registries::session()->set(SessionKey::USER_ID, $userData->id);
            Registries::session()->set(SessionKey::USER_EMAIL, $userData->email);
            Registries::session()->set(SessionKey::USER_USERNAME, $userData->username);
            Registries::session()->set(SessionKey::USER_ROLE, $userData->role->id);

            if ($rememberMe) {
                $tokenRows = static::select([UserField::EMAIL->field() => $email], [UserField::TOKEN->field()], 1);
                $token = $tokenRows[0][UserField::TOKEN->field()] ?? null;

                if ($token !== null) {
                    Registries::cookie()->set(
                        CookieKey::USER_TOKEN,
                        $token,
                        self::REMEMBER_ME_TIME
                    );
                }
            }

            static::update([UserField::LAST_LOGIN->field() => date(self::DATE_FORMAT)], [UserField::ID->field() => $userData->id]);

            return $userData;
        }

        return null;
    }

    public static function generateToken(): string
    {
        try {
            return bin2hex(random_bytes(self::SECURITY_STAMP_LENGTH / 2));
        } catch (RandomException $e) {
            throw new UnexpectedValueException("Failed to generate secure token", 0, $e);
        }
    }

    private static function fromRow(array $row): static
    {
        return new static(
            $row[UserField::EMAIL->field()],
            $row[UserField::USERNAME->field()],
            $row[UserField::PASSWORD->field()],
            (int)$row[UserField::ROLE_ID->field()],
            false,
            (int)$row[UserField::ID->field()]
        );
    }

}