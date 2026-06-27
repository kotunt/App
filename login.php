<?php
// login.php

// 1. Bootstrap the application to set up the environment
require_once __DIR__ . '/bootstrap.php';

// 2. Instantiate and run the controller.
require_once __DIR__ . '/src/controllers/LoginController.php';

$controller = new LoginController();
$controller->handleRequest();