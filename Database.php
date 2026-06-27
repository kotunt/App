<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $conn;

    private function __construct()
    {
        $this->connect();
    }

    private function connect(): void
    {
        $driver = DB_DRIVER;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            if ($driver === 'sqlite') {
                $db_path = DB_PATH;
                $dir = dirname($db_path);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                $this->conn = new PDO("sqlite:" . $db_path, null, null, $options);
            } else { // Default to mysql
                $dsn = sprintf("mysql:host=%s;dbname=%s;charset=utf8mb4", DB_HOST, DB_NAME);
                $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            }
        } catch (PDOException $e) {
            // In a real app, you'd want to log this error and show a generic error page.
            error_log("Database connection failed: " . $e->getMessage());
            if (APP_ENV !== 'production') {
                die("Database connection failed: " . $e->getMessage());
            }
            die("Database connection unavailable.");
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }
}