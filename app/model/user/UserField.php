<?php

namespace TugasAkhir\model\user;

use TugasAkhir\core\data\DataField;
use TugasAkhir\core\data\EnumSchemaBuilder;

enum UserField implements DataField
{
    public const int SECURITY_STAMP_LENGTH = 32;
    case ID;
    case EMAIL;
    case USERNAME;
    case PASSWORD;
    case ROLE_ID;
    case LAST_LOGIN;
    case CREATED_AT;
    case UPDATED_AT;
    case TOKEN;

    public function field(): string
    {
        return strtolower($this->name);
    }

    public function getDefinition(): string
    {
        return match ($this) {
            self::ID => "INT AUTO_INCREMENT PRIMARY KEY",
            self::EMAIL => "VARCHAR(100) NOT NULL UNIQUE",
            self::USERNAME => "VARCHAR(50) NOT NULL",
            self::PASSWORD => "VARCHAR(255) NOT NULL",
            self::ROLE_ID => "INT NOT NULL",
            self::LAST_LOGIN => "DATETIME",
            self::CREATED_AT, self::UPDATED_AT => "DATETIME DEFAULT CURRENT_TIMESTAMP",
            self::TOKEN => "VARCHAR(" . self::SECURITY_STAMP_LENGTH . ") NOT NULL"
        };
    }

    use EnumSchemaBuilder;
}