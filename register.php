<?php
// register.php

require_once __DIR__ . '/src/controllers/RegisterController.php';

$controller = new RegisterController();
$controller->handleRequest();
