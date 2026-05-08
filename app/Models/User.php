<?php

class User extends DataModel
{

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

    public readonly string $password;

    public function __construct(
        public readonly string $email,
        public readonly string $username,
        string                 $rawPassword,
        public readonly int    $roleId
    )
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address");
        }

        if (!empty(self::select(['email' => $email], 'id', 1))) {
            throw new UnexpectedValueException("Email address is already registered.");
        }

        if (!empty(self::select(['username' => $username], 'id', 1))) {
            throw new UnexpectedValueException("Username is already taken.");
        }

        $this->password = password_hash($rawPassword, PASSWORD_DEFAULT);
        self::insert([
            "email" => $this->email,
            "username" => $this->username,
            "password" => $this->password,
            "role_id" => $this->roleId
        ]);
    }

    public static function get(array $condition): ?self
    {
        $data = self::select($condition, '*', 1);
        if (empty($data)) {
            return null;
        }
        return $data[0];
    }

}
