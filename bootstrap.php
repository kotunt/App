<?php

use App\Core\Database;
use App\Core\Session;

// 1. Set Default Timezone
date_default_timezone_set('Asia/Yangon');

// 2. Load Composer Autoloader
require_once __DIR__ . '/vendor/autoload.php';

// 3. Setup Error & Exception Handling (as early as possible)
ini_set('display_errors', 0); // Default to off
error_reporting(E_ALL);

// 4. Load Environment Variables from .env
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad(); // Use safeLoad to avoid errors if .env is missing
    
    // Set error display based on environment
    if (getenv('APP_ENV') !== 'production') {
        ini_set('display_errors', 1);
    }
} catch (Throwable $e) {
    // Silently ignore if dotenv is not installed or fails
    ini_set('display_errors', 1); // Show errors if dotenv fails in dev
}

set_exception_handler(function($e) {
    error_log("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());
    if (getenv('APP_ENV') !== 'production') {
        echo "<b>Fatal error:</b> Uncaught exception '" . get_class($e) . "' with message '" . $e->getMessage() . "' in " . $e->getFile() . ":" . $e->getLine();
    } else {
        // You can show a generic error page here
        http_response_code(500);
        echo "A system error occurred. Please try again later.";
    }
});

// 5. Load Global Configuration (this defines constants like DB_HOST)
require_once __DIR__ . '/core/config.php';

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
Session::start();

// 8. Load Language Helper
require_once __DIR__ . '/lang/language.php';

// 8b. Load Auth Helper (provides require_admin, require_admin_login, require_main_admin, etc.)
require_once __DIR__ . '/core/auth_helper.php';

// 9. Maintenance Mode Check
$current_script = basename($_SERVER['PHP_SELF']);
$allowed_scripts = ['maintenance.php', 'login.php', 'logout.php'];

if (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE === '1' && !in_array($current_script, $allowed_scripts, true)) {
    $is_admin = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'sub_admin']);
    if (!$is_admin) {
        header("Location: /maintenance.php");
        exit();
    }
}