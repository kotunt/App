<?php

// 1. Bootstrap the application
require_once __DIR__ . '/bootstrap.php';

// 2. Use the Database connection
use App\Core\Database;
$conn = Database::getInstance()->getConnection();

// 3. Load Controller, View, etc.
require_once __DIR__ . '/src/controllers/HomeController.php';
$page_title = __('home_page_title');
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/src/views/home_view.php';
require_once __DIR__ . '/includes/footer.php';
?>
