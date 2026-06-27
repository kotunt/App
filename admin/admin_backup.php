<?php

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;
$conn = Database::getInstance()->getConnection();

// Main Admin သာ ဝင်ခွင့်ပြုမည်
require_main_admin();

// Database ကြီးမားပါက Time Out မဖြစ်စေရန် သတ်မှတ်ခြင်း
set_time_limit(0);

// Backup ယူကြောင်း မှတ်တမ်းတင်မည်
log_activity($_SESSION['user_id'], 'DATABASE_BACKUP', 'Downloaded database backup.');

if (DB_DRIVER === 'sqlite') {
    // For SQLite, the backup is simply the database file itself.
    $db_path = DB_PATH;
    if (file_exists($db_path)) {
        $filename = "thai2d3d_backup_" . date("Y-m-d_H-i-s") . ".sqlite";
        header('Content-Type: application/x-sqlite3');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($db_path));
        readfile($db_path);
        exit();
    } else {
        die("SQLite database file not found.");
    }
} else {
    // For MySQL, generate a SQL dump.
    $filename = "thai2d3d_backup_" . date("Y-m-d_H-i-s") . ".sql";
    
    header('Content-Type: application/sql; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    
    $output = fopen('php://output', 'w');
    
    fwrite($output, "-- Thai 2D3D Database Backup (MySQL)\n");
    fwrite($output, "-- Generated: " . date("Y-m-d H:i:s") . "\n\n");
    fwrite($output, "SET NAMES utf8mb4;\n");
    fwrite($output, "SET FOREIGN_KEY_CHECKS = 0;\n\n");
    
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    foreach ($tables as $table) {
        $result = $conn->query("SELECT * FROM `$table`");
        
        fwrite($output, "DROP TABLE IF EXISTS `$table`;\n");
        $create_table_stmt = $conn->query("SHOW CREATE TABLE `$table`");
        $row2 = $create_table_stmt->fetch(PDO::FETCH_ASSOC);
        fwrite($output, $row2['Create Table'] . ";\n\n");
        
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
            $insert_query = "INSERT INTO `$table` VALUES(";
            $values = [];
            foreach($row as $value) {
                if (is_null($value)) {
                    $values[] = 'NULL';
                } else {
                    $values[] = "'" . $conn->quote($value) . "'";
                }
            }
            $insert_query .= implode(',', $values) . ");\n";
            fwrite($output, $insert_query);
        }
        fwrite($output, "\n\n");
    }
    
    fwrite($output, "SET FOREIGN_KEY_CHECKS = 1;\n");
    fclose($output);
    exit();
}