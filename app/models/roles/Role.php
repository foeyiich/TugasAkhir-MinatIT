<?php

namespace TugasAkhir\models\roles;

use InvalidArgumentException;
use RuntimeException;
use TugasAkhir\App;
use TugasAkhir\core\Database;
use TugasAkhir\models\DataModel;
use TugasAkhir\utils\UtilityClass;

class Role extends DataModel
{

    protected static function getDatabase(): ?Database
    {
        return App::getInstance()->mainDatabase;
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

        $createdRole = self::findById($id);

        if ($createdRole === null) {
            throw new RuntimeException("Error bikin role dengan id $id");
        }

        return $createdRole;
    }

    public static function permissionsToJson(array $permissions): string
    {
        return json_encode(array_map(
            fn($permission) => $permission instanceof Permission ? $permission->name : $permission,
            $permissions
        ));
    }

    public static function permissionsFromJson(string $json): array
    {
        $permissionNames = json_decode($json, true) ?? [];
        return array_map(
            fn($permissionName) => constant(Permission::class . "::" . $permissionName),
            $permissionNames
        );
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
