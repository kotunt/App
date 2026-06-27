<?php
// forgot_password.php

require_once __DIR__ . '/src/controllers/ForgotPasswordController.php';

$controller = new ForgotPasswordController();
$controller->handleRequest();
