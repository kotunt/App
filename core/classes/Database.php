<?php

class Database {
    private static $instance = null;
    private $connection;

    private $servername;
    private $username_db;
    private $password_db;
    private $dbname;

    private function __construct() {
        require_once __DIR__ . '/../config.php';
        
        $this->servername = DB_HOST;
        $this->username_db = DB_USER;
        $this->password_db = DB_PASS;
        $this->dbname = DB_NAME;

        try {
            $this->connection = new mysqli($this->servername, $this->username_db, $this->password_db, $this->dbname);

            if ($this->connection->connect_error) {
                throw new Exception("Database Connection Failed: " . $this->connection->connect_error);
            }

            $this->connection->set_charset("utf8mb4");
            $this->connection->query("SET time_zone = '+06:30'");

        } catch (Exception $e) {
            // Log the detailed error for the admin
            error_log("[" . date("Y-m-d H:i:s") . "] " . $e->getMessage() . "
", 3, dirname(__DIR__, 2) . '/logs/errorlog.txt');
            
            // Show a generic error message to the user and stop the script
            $db_error_msg = function_exists('__') ? __('db_connection_error') : "စနစ်တွင် ယာယီအမှားအယွင်း ဖြစ်ပေါ်နေပါသည်။ ခေတ္တစောင့်ဆိုင်းပြီး ထပ်မံကြိုးစားပါ။";
            die($db_error_msg);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Prevent cloning the instance
    private function __clone() {}

    // Prevent unserializing the instance
    public function __wakeup() {}
}
