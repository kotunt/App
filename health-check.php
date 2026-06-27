<?php
http_response_code(200);
header('Content-Type: application/json');

// Optionally, you can add a database check for a more thorough health check.
// try {
//     require_once __DIR__ . '/core/db_connect.php';
//     $conn = Database::getInstance()->getConnection();
//     $conn->query("SELECT 1");
//     $db_status = 'ok';
// } catch (Exception $e) {
//     http_response_code(503); // Service Unavailable
//     $db_status = 'error';
// }

echo json_encode([
    'status' => 'ok',
    // 'db' => $db_status,
    'timestamp' => date('Y-m-d H:i:s')
]);