<?php

namespace Test\model;

use Test\Test;
use Test\TestCase;
use TugasAkhir\core\data\Database;
use TugasAkhir\core\registry\Registries;
use TugasAkhir\core\registry\SessionManager;
use TugasAkhir\model\role\Role;
use TugasAkhir\model\user\CreateAccountData;
use TugasAkhir\model\user\User;

// CURRENTLY BREAK
class UserTest extends TestCase
{

    public function onStart(): void
    {
        echo "Session Start.\n";
        SessionManager::getInstance()->start();
    }

    public function onStop(): void
    {
        echo "Session Stop.\n";
        SessionManager::getInstance()->destroy();
    }

    private function setupDatabase(): void
    {
        Registries::setMainDatabase(Database::create('sqlite::memory:'));

        Role::init();
        Role::seedDefaults();
        User::init();
    }

    #[Test]
    public function testUserRegistration(): void
    {
        $this->setupDatabase();
        $data = new CreateAccountData(
            "test@example.com",
            "testuser",
            "password123",
            1 // Guru Role ID
        );

        $user = User::createAccount($data);
        $this->assertEquals("testuser", $user->username, "User should be created with correct username.");
        $this->assertTrue(User::exists(['email' => "test@example.com"]), "User should exist in database.");
    }

    #[Test]
    public function testUserAuthentication(): void
    {
        $this->setupDatabase();
        
        $data = new CreateAccountData("test@example.com", "testuser", "password123", 1);
        User::createAccount($data);

        $user = User::authenticate("test@example.com", "password123");
        $this->assertNotNull($user, "Authentication should succeed with correct credentials.");

        $fail = User::authenticate("test@example.com", "wrongpassword");
        $this->assertNull($fail, "Authentication should fail with incorrect password.");
    }

    #[Test]
    public function testFindUserByRole(): void
    {
        $this->setupDatabase();

        $data = new CreateAccountData("test@example.com", "testuser", "password123", 1);
        User::createAccount($data);

        $users = User::findByRole(1);
        $this->assertTrue(count($users) > 0, "Should find at least one user with role ID 1.");
    }

    #[Test]
    public function testSqlInjection(): void
    {
        $this->setupDatabase();
        $maliciousEmail = "test@example.com' OR '1'='1";
        $injectedUser = User::authenticate($maliciousEmail, "any_password");

        $this->assertNull($injectedUser, "Authentication should fail and protect against SQL injection.");

        $maliciousPayload = "nonexistent@example.com' OR 1=1 --";
        $exists = User::exists(['email' => $maliciousPayload]);

        $this->assertFalse($exists, "Exists check should return false and sanitize malicious input.");

    }
}