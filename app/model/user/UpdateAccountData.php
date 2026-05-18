<?php

namespace TugasAkhir\model\user;

use InvalidArgumentException;
use TugasAkhir\model\role\Role;

final class UpdateAccountData
{
    public readonly ?string $email;
    public readonly ?string $username;

    public function __construct(
        ?string                       $email = null,
        ?string                       $username = null,
        public readonly Role|int|null $role = null
    )
    {
        if ($email !== null) {
            $email = trim($email);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException("Invalid email address");
            }
        }

        if ($username !== null) {
            $username = trim($username);

            if ($username === '') {
                throw new InvalidArgumentException("Username cannot be empty");
            }
        }

        $this->email = $email;
        $this->username = $username;
    }

    public function hasChanges(): bool
    {
        return $this->email !== null || $this->username !== null || $this->role !== null;
    }
}
