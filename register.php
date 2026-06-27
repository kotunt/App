<?php
// register.php

require_once __DIR__ . '/src/controllers/RegisterController.php';

$controller = new RegisterController();
$controller->handleRequest();

// After successful registration inside the controller, you would call:
/*
if ($registration_was_successful) {
    \App\Core\RealtimeNotifier::publish('new_user', ['username' => $username, 'phone' => $phone_number]);
}
*/
