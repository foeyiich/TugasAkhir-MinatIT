<?php

namespace TugasAkhir\model\role;

use TugasAkhir\core\data\DataField;
use TugasAkhir\core\data\EnumSchemaBuilder;

enum RoleField implements DataField
{

    case ID;
    case NAME;
    case DESCRIPTION;
    case PERMISSIONS;

    public function field(): string
    {
        return strtolower($this->name);
    }

    public function getDefinition(): string
    {
        return match ($this) {
            self::ID => "INT AUTO_INCREMENT PRIMARY KEY",
            self::NAME => "VARCHAR(50) NOT NULL",
            self::DESCRIPTION => "VARCHAR(255) NOT NULL",
            self::PERMISSIONS => "JSON NOT NULL"
        };
    }

    use EnumSchemaBuilder;
}