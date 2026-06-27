<?php
// login.php

// 1. Bootstrap the application to set up the environment
require_once __DIR__ . '/bootstrap.php';

// 2. Use the controller. The autoloader from bootstrap.php will handle including the file.
use App\Controllers\LoginController;

$controller = new LoginController();
$controller->handleRequest();