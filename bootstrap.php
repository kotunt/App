<?php

// 1. Set Default Timezone
date_default_timezone_set('Asia/Yangon');

// 3. Setup Error & Exception Handling (as early as possible)
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/errorlog.txt');

if (getenv('APP_ENV') !== 'production') {
    ini_set('display_errors', 1);
} else {
    ini_set('display_errors', 0);
}

set_exception_handler(function($e) {
    error_log("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());
    if (getenv('APP_ENV') !== 'production') {
        // In development, show detailed error
        http_response_code(500);
        echo "<h1>Fatal Error</h1><p><b>Uncaught exception:</b> " . get_class($e) . "</p><p><b>Message:</b> " . htmlspecialchars($e->getMessage()) . "</p><p><b>Location:</b> " . $e->getFile() . " on line " . $e->getLine() . "</p><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        // In production, show a generic error page
        http_response_code(500);
        echo "A system error occurred. Please try again later.";
    }
});

// 5. Load Global Configuration (this defines constants like DB_HOST)
require_once __DIR__ . '/core/config.php';

// Load Core Classes manually since Composer is removed
require_once __DIR__ . '/core/classes/Database.php';

// Load the global Database class used by legacy controllers
require_once __DIR__ . '/core/classes/Database.php';

// Check for required extensions
if (!extension_loaded('mysqli')) {
    http_response_code(500);
    die("Error: The 'mysqli' extension is not loaded. Please check your PHP configuration.");
}

// 6. Establish Database Connection
$conn = Database::getInstance()->getConnection();

// 7. Start Session (now that DB connection is confirmed)
if (session_status() === PHP_SESSION_NONE) {
    // Note: Redis session handler is removed as it was part of the Composer setup.
    // It will now use the default file-based session handler.
    session_start();
}

// 8. Load Language Helper
require_once __DIR__ . '/lang/language.php';

// 8b. Load Auth Helper (provides require_admin, require_admin_login, require_main_admin, etc.)
require_once __DIR__ . '/core/auth_helper.php';

// 9. Maintenance Mode Check
$current_script = basename($_SERVER['PHP_SELF']);
$allowed_scripts = ['maintenance.php', 'login.php', 'logout.php'];

if (getenv('MAINTENANCE_MODE') === '1' && !in_array($current_script, $allowed_scripts, true)) {
    $is_admin = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'sub_admin']);
    if (!$is_admin) {
        header("Location: /maintenance.php");
        exit();
    }
}