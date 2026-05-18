<?php

namespace TugasAkhir\model\role;

use InvalidArgumentException;
use TugasAkhir\core\data\Database;
use TugasAkhir\core\registry\Registries;
use TugasAkhir\model\DataModel;
use TugasAkhir\utility\UtilityClass;
use UnexpectedValueException;

class Role extends DataModel
{

    protected static function getDatabase(): Database
    {
        return Registries::getMainDatabase();
    }

    protected static function getTableName(): string
    {
        return "roles";
    }

    protected static function getSchema(): array
    {
        return RoleField::buildSchema();
    }

    public function __construct(
        public string $name,
        public string $description,
        public array  $permissions = [],
        public ?int   $id = null
    )
    {
        if (empty($name)) {
            throw new InvalidArgumentException("Name cannot be empty");
        }
        UtilityClass::validateListArray($this->permissions);
        $this->permissions = array_unique($this->permissions, SORT_REGULAR);
    }

    public static function findById(int $id): ?self
    {
        $data = self::select(['id' => $id], '*', 1);
        if (empty($data)) {
            return null;
        }
        $r = $data[0];
        return new self(
            $r['name'],
            $r['description'],
            self::permissionsFromJson($r['permissions']),
            (int)$r['id']
        );
    }

    public static function getOrWrite(int $id, self $role): self
    {
        $value = self::findById($id);
        if ($value !== null) {
            return $value;
        }

        self::insert([
            "id" => $id,
            "name" => $role->name,
            "description" => $role->description,
            "permissions" => self::permissionsToJson($role->permissions),
        ]);

        return self::findById($id) ?? throw new UnexpectedValueException("Unable to create role.");
    }

    public static function permissionsToJson(array $permissions): string
    {
        UtilityClass::validateListArray($permissions);
        return json_encode(array_map(
            static fn($p) => $p instanceof Permission ? $p->name : (string)$p,
            $permissions
        ), JSON_THROW_ON_ERROR);
    }

    public static function permissionsFromJson(string $json): array
    {
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR) ?? [];
    }

    public static function seedDefaults(): void
    {
        self::getOrWrite(1, new self("Guru", "Admin guru", [
            Permission::UPDATE_OWN_PASSWORD,
            Permission::MANAGE_GRADES,
            Permission::MANAGE_ATTENDANCE,
        ]));

        self::getOrWrite(2, new self("Siswa", "User siswa", [
            Permission::UPDATE_OWN_PASSWORD,
            Permission::READ_OWN_GRADES,
            Permission::MANAGE_OWN_DOCUMENTS,
        ]));

        self::getOrWrite(3, new self("Kurikulum", "Super admin", [
            Permission::UPDATE_OWN_PASSWORD,
            Permission::MANAGE_GRADES,
            Permission::MANAGE_ATTENDANCE,
            Permission::MANAGE_ACCOUNTS,
            Permission::MANAGE_PPM_FORMS,
            Permission::MANAGE_ANNOUNCEMENTS,
            Permission::MANAGE_ADMIN_DOCUMENTS,
        ]));
    }

}
