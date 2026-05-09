<?php

namespace TugasAkhir\models\roles;

use InvalidArgumentException;
use TugasAkhir\core\Database;
use TugasAkhir\core\Registries;
use TugasAkhir\models\DataModel;
use TugasAkhir\utils\UtilityClass;

class Role extends DataModel
{

    protected static function getDatabase(): ?Database
    {
        return Registries::get("mainDB");
    }

    protected static function getTableName(): string
    {
        return "roles";
    }

    protected static function getSchema(): array
    {
        return [
            "id" => "INT AUTO_INCREMENT PRIMARY KEY",
            "name" => "VARCHAR(50) NOT NULL",
            "description" => "VARCHAR(255) NOT NULL",
            "permissions" => "JSON NOT NULL",
        ];
    }

    public function __construct(
        public string $name,
        public string $description,
        public array  $permissions = [],
        public ?int   $id = null
    )
    {
        UtilityClass::validateListArray($permissions);
        if (empty($name))
            throw new InvalidArgumentException("Name cannot be empty");
    }

    public static function findById(int $id): ?self
    {
        $data = self::select(['id' => $id], '*', 1);
        if (empty($data)) return null;
        $r = $data[0];
        return new self($r['name'], $r['description'], json_decode($r['permissions'], true), (int)$r['id']);
    }

    public static function getOrWrite(int $id, self $role): self
    {
        $value = self::findById($id);
        if (is_null($value)) {
            self::insert([
                "id" => $id,
                "name" => $role->name,
                "description" => $role->description,
                "permissions" => $role->permissions
            ]);
        }
        return $value;
    }

}
