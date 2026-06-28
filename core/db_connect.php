<?php

// --- Global Configuration & Helpers ---
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Database.php';

// Establish the shared database connection (singleton) for the entire request.
// This is assigned unconditionally so every script that includes this file can
// rely on $conn at global scope.
$conn = Database::getInstance()->getConnection();

?>