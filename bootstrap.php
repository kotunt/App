<?php

// 1. Set Default Timezone
date_default_timezone_set('Asia/Yangon');

// 2. Load Composer Autoloader
require_once __DIR__ . '/vendor/autoload.php';

// 3. Load Environment Variables from .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad(); // Use safeLoad to avoid errors if .env is missing
} catch (Throwable $e) {
    // Silently ignore if dotenv is not installed or fails
}

// 4. Load Global Configuration (this defines constants like DB_HOST)
require_once __DIR__ . '/core/config.php';

// 5. Setup Error & Exception Handling
if (APP_ENV === 'production') {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/errorlog.txt');
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

set_exception_handler(function($e) {
    error_log("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());
    if (APP_ENV !== 'production') {
        echo "<b>Fatal error:</b> Uncaught exception '" . get_class($e) . "' with message '" . $e->getMessage() . "' in " . $e->getFile() . ":" . $e->getLine();
    } else {
        // You can show a generic error page here
        http_response_code(500);
        echo "A system error occurred. Please try again later.";
    }
});

// 6. Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 7. Load Language Helper
require_once __DIR__ . '/lang/language.php';

// 8. Maintenance Mode Check (moved from db_connect.php)
$current_script = basename($_SERVER['PHP_SELF']);
$allowed_scripts = ['maintenance.php', 'login.php', 'logout.php'];

if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE === '1' && !in_array($current_script, $allowed_scripts)) {
    $is_admin = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'sub_admin']);
    if (!$is_admin) {
        header("Location: /maintenance.php");
        exit();
    }
}