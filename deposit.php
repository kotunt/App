<?php
require_once __DIR__ . '/src/controllers/DepositController.php';

$controller = new DepositController();
$controller->handleRequest();

// After successful deposit request inside the controller, you would call:
/*
if ($deposit_request_was_successful) {
    \App\Core\RealtimeNotifier::publish('new_deposit', ['username' => $user['username'], 'amount' => $amount]);
}
*/