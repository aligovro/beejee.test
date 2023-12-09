<?php

namespace app\database;

class CreateTables
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function run()
    {
        $this->createTasksTable();
        $this->createUsersTable();
    }

    private function createTasksTable()
    {
        $prefix = $this->db->getOptions()['db_prefix'];

        $query = "CREATE TABLE IF NOT EXISTS {$prefix}tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        text TEXT NOT NULL,
        status BOOLEAN DEFAULT 0
    )";

        $this->db->getMysqli()->query($query);
    }

    private function createUsersTable()
    {
        $prefix = $this->db->getOptions()['db_prefix'];

        $query = "CREATE TABLE IF NOT EXISTS {$prefix}users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        password_hash VARCHAR(255) NOT NULL
    )";

        $this->db->getMysqli()->query($query);
    }
}
