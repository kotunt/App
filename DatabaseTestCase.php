<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use PDO;

abstract class DatabaseTestCase extends TestCase
{
    protected static ?PDO $pdo = null;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        // Load config constants like DB_DRIVER, DB_PATH
        require_once __DIR__ . '/../core/config.php';
    }

    public function getPdo(): PDO
    {
        if (self::$pdo === null) {
            // Use the environment variables set in phpunit.xml
            self::$pdo = new PDO('sqlite::memory:');
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
    }

    protected function tearDown(): void
    {
        // In-memory database is destroyed automatically, but we can unset for clarity
        self::$pdo = null;
        parent::tearDown();
    }

    private function createSchema(): void
    {
        $schemaSql = file_get_contents(__DIR__ . '/database/schema.sql');
        $this->getPdo()->exec($schemaSql);
    }
}