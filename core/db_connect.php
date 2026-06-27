<?php
// Core application bootstrap
// ---

// Include Composer's autoloader if it exists
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// --- Global Configuration & Helpers ---
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Database.php';


// Set the default timezone for all date/time functions
date_default_timezone_set('Asia/Yangon');

// --- Global Error & Exception Handling ---
// Disable displaying errors to the user for security
ini_set('display_errors', 0);
// Enable logging errors to a file
ini_set('log_errors', 1);
// Specify the error log file path
ini_set('error_log', dirname(__DIR__) . '/logs/errorlog.txt');

// Set a custom exception handler to log uncaught exceptions
set_exception_handler(function($e) {
    $log_msg = "[" . date("Y-m-d H:i:s") . "] Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine() . "\n";
    error_log($log_msg, 3, dirname(__DIR__) . '/logs/errorlog.txt');
});

// Set a custom error handler to log PHP errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // This error code is not included in error_reporting
    if (!(error_reporting() & $errno)) {
        return false;
    }
    $log_msg = "[" . date("Y-m-d H:i:s") . "] Error: $errstr in $errfile on line $errline\n";
    error_log($log_msg, 3, dirname(__DIR__) . '/logs/errorlog.txt');
    return true;
});
// -----------------------------------


// --- Maintenance Mode Check ---
$current_script = basename($_SERVER['PHP_SELF']);
// Scripts that are allowed to run during maintenance mode
$allowed_scripts = ['maintenance.php', 'login.php', 'logout.php']; 

if (!in_array($current_script, $allowed_scripts)) {
    try {
        $conn = Database::getInstance()->getConnection();
        $m_stmt = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'");
        
        if ($m_stmt && $m_stmt->num_rows > 0) {
            $m_row = $m_stmt->fetch_assoc();
            if ($m_row['setting_value'] === '1') {
                // If session is not already started, start it to check user role
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                // If the user is not an admin or sub-admin, redirect to the maintenance page
                $is_admin = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'sub_admin']);
                
                if (!$is_admin) {
                    // Before redirecting, close the database connection
                    if ($m_stmt instanceof mysqli_result) {
                        $m_stmt->close();
                    }
                    // It's good practice to close the connection if the script is ending.
                    // However, the singleton instance will live for the request duration.
                    
                    header("Location: maintenance.php");
                    exit();
                }
            }
        }
    } catch (Exception $e) {
        // If the database connection fails during maintenance check, log it and die.
        error_log("Maintenance check failed: " . $e->getMessage());
        die("An error occurred while checking system status.");
    }
}
?>