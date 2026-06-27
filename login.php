<?php
// login.php

require_once __DIR__ . '/src/controllers/LoginController.php';

$controller = new LoginController();
$controller->handleRequest();