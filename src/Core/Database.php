<?php

namespace App\Core;

use mysqli;
use mysqli_sql_exception;

class Database
{
    private static ?Database $instance = null;
    private ?mysqli $conn = null;

    private function __construct()
    {
        try {
            // Suppress errors to handle them manually
            mysqli_report(MYSQLI_REPORT_OFF);
            
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->conn->connect_error) {
                throw new mysqli_sql_exception($this->conn->connect_error, $this->conn->connect_errno);
            }
            
            $this->conn->set_charset("utf8mb4");
            
        } catch (mysqli_sql_exception $e) {
            // In a real app, you'd want to log this error and show a generic error page.
            error_log("Database connection failed: " . $e->getMessage());
            if (getenv('APP_ENV') !== 'production') {
                die("Database connection failed: " . $e->getMessage());
            }
            die("Database connection unavailable.");
        }
    }

    /**
     * Gets the single instance of the Database class.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): mysqli
    {
        return $this->conn;
    }
}