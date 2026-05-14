<?php

namespace TugasAkhir\models\users;

use InvalidArgumentException;
use TugasAkhir\models\roles\Role;

final class CreateAccountData
{
    public readonly string $email;
    public readonly string $username;
    public readonly string $password;

    public function __construct(
        string $email,
        string $username,
        string $password,
        public readonly Role|int $role
    )
    {
        $email = trim($email);
        $username = trim($username);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address");
        }

        if ($username === '') {
            throw new InvalidArgumentException("Username cannot be empty");
        }

        if ($password === '') {
            throw new InvalidArgumentException("Password cannot be empty");
        }

        $this->email = $email;
        $this->username = $username;
        $this->password = $password;
    }
}
