<?php

// 1. Bootstrap the application
require_once __DIR__ . '/bootstrap.php'; // This handles DB connection, sessions, etc.

// 2. Instantiate and run the appropriate controller
require_once __DIR__ . '/src/controllers/HomeController.php';

$controller = new HomeController();
$controller->show(); // Assuming a 'show' method exists in HomeController to render the page
