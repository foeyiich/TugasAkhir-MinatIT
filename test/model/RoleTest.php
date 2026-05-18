<?php

namespace Test\model;

use Test\Test;
use Test\TestCase;
use TugasAkhir\core\data\Database;
use TugasAkhir\core\registry\Registries;
use TugasAkhir\model\role\Permission;
use TugasAkhir\model\role\Role;

class RoleTest extends TestCase
{

    public function onStart(): void
    {
        $db = Database::create('sqlite::memory:');
        Registries::setMainDatabase($db);
    }

    #[Test]
    public function testInitialization(): void
    {
        Role::init();
        $this->pass("Role table initialized successfully.");
    }

    #[Test]
    public function testRoleCreation(): void
    {
        $role = new Role("Test Role", "Description", [Permission::MANAGE_ACCOUNTS]);
        Role::insert([
            'id' => 10,
            'name' => $role->name,
            'description' => $role->description,
            'permissions' => Role::permissionsToJson($role->permissions)
        ]);

        $found = Role::findById(10);
        $this->assertEquals("Test Role", $found->name, "Role name should match.");
        $this->assertEquals(1, count($found->permissions), "Should have 1 permission.");
    }

    #[Test]
    public function testPermissionsJson(): void
    {
        $perms = [Permission::MANAGE_GRADES, Permission::MANAGE_ATTENDANCE];
        $json = Role::permissionsToJson($perms);

        $decoded = Role::permissionsFromJson($json);
        $this->assertEquals("MANAGE_GRADES", $decoded[0], "JSON decoding should preserve permission names.");
    }

    #[Test]
    public function testSeedDefaults(): void
    {
        Role::seedDefaults();
        $kurikulum = Role::findById(3);

        $this->assertEquals("Kurikulum", $kurikulum->name, "Default seed 'Kurikulum' should exist.");
    }
}