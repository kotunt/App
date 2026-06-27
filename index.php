<?php
// Load Composer autoloader and .env (if present) for local development
// Note: In production, provide environment variables via your process (php-fpm pool, container env, etc.)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->safeLoad(); // don't throw if no .env
    } catch (Throwable $e) {
        // ignore
    }
}

session_start();
var_dump(__DIR__); exit;
require_once __DIR__ . '/core/db_connect.php';
require_once __DIR__ . '/lang/language.php';
require_once __DIR__ . '/src/controllers/HomeController.php';
$page_title = __('home_page_title');
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/src/views/home_view.php';
require_once __DIR__ . '/includes/footer.php';
?>
