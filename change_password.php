<?php
// change_password.php

require_once __DIR__ . '/src/controllers/ChangePasswordController.php';

$controller = new ChangePasswordController();
$controller->handleRequest();