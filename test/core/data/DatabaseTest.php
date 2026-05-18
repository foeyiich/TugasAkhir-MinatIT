<?php

namespace Test\core\data;

use Test\TestCase;
use TugasAkhir\core\data\Database;

class DatabaseTest extends TestCase
{
    private Database $db;

    public function run(): void
    {
        echo "Connecting to database...\n";
        $this->db = Database::create('sqlite::memory:');

        $this->testCreateTable();
        $this->testInsertAndSelect();
        $this->testUpdate();
        $this->testDelete();
    }

    private function testCreateTable(): void
    {
        $sql = "CREATE TABLE test (id INTEGER AUTO_INCREMENT PRIMARY KEY, name TEXT)";
        $this->db->exec($sql);
        $this->pass("Table created successfully");
    }

    private function testInsertAndSelect(): void
    {
        $data = ['id' => 1, 'name' => 'Juan'];
        $this->db->insert('test', $data);

        $where = ['id' => 1];
        $results = $this->db->select('test', $where);

        $this->assertEquals(1, count($results), "Should return 1 row");
        $this->assertEquals('Juan', $results[0]['name'], "Name should match");
    }

    private function testUpdate(): void
    {
        $set = ['name' => 'Anderson'];
        $where = ['id' => 1];
        $this->db->update('test', $set, $where);

        $results = $this->db->select('test', $where);
        $this->assertEquals('Anderson', $results[0]['name'], "Name should be updated");
    }

    private function testDelete(): void
    {
        $where = ['id' => 1];
        $this->db->delete('test', $where);

        $this->assertFalse($this->db->exists('test', $where), "Row should no longer exist");
    }
}