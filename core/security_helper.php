<?php
// security_helper.php

/**
 * Checks if the user's IP is rate-limited for login attempts.
 * Limit is set to 5 attempts per 15 minutes.
 * @param mysqli $conn The database connection object.
 */
function check_login_rate_limit($conn, $ip_address, $phone_number) {
    $time_limit = date('Y-m-d H:i:s', strtotime('-15 minutes'));
    
    $stmt = $conn->prepare("SELECT attempts FROM login_attempts WHERE ip_address = ? AND phone_number = ? AND last_attempt > ?");
    $stmt->bind_param("sss", $ip_address, $phone_number, $time_limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['attempts'] >= 5) {
            return false; // Rate limited
        }
    }
    return true; // OK to proceed
}

/**
 * Records a failed login attempt.
 * @param mysqli $conn The database connection object.
 */
function record_failed_login($conn, $ip_address, $phone_number) {
    $current_time = date('Y-m-d H:i:s');
    
    // Check if a record exists
    $stmt = $conn->prepare("SELECT id, attempts FROM login_attempts WHERE ip_address = ? AND phone_number = ?");
    $stmt->bind_param("ss", $ip_address, $phone_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Update existing
        $new_attempts = $row['attempts'] + 1;
        $update_stmt = $conn->prepare("UPDATE login_attempts SET attempts = ?, last_attempt = ? WHERE id = ?");
        $update_stmt->bind_param("isi", $new_attempts, $current_time, $row['id']);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // Insert new
        $insert_stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, phone_number, attempts, last_attempt) VALUES (?, ?, 1, ?)");
        $insert_stmt->bind_param("sss", $ip_address, $phone_number, $current_time);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $stmt->close();
}

/**
 * Checks if the user's PIN is rate-limited.
 * Limit is set to 5 attempts per 15 minutes.
 * @param mysqli $conn The database connection object.
 */
function check_pin_rate_limit($conn, $user_id) {
    $time_limit = date('Y-m-d H:i:s', strtotime('-15 minutes'));
    
    $stmt = $conn->prepare("SELECT attempts FROM pin_attempts WHERE user_id = ? AND last_attempt > ?");
    $stmt->bind_param("is", $user_id, $time_limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['attempts'] >= 5) return false; // Locked
    }
    return true; // OK
}

/**
 * Records a failed PIN attempt for a user.
 * @param mysqli $conn The database connection object.
 */
function record_failed_pin($conn, $user_id) {
    $current_time = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("INSERT INTO pin_attempts (user_id, attempts, last_attempt) VALUES (?, 1, ?) 
                            ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = ?");
    // types: i, s, s
    $stmt->bind_param("iss", $user_id, $current_time, $current_time);
    $stmt->execute();
    $stmt->close();
}

/**
 * Clears failed PIN attempts after a successful verification.
 * @param mysqli $conn The database connection object.
 */
function clear_failed_pins($conn, $user_id) {
    $stmt = $conn->prepare("DELETE FROM pin_attempts WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

/**
 * Clears failed login attempts after a successful login.
 * @param mysqli $conn The database connection object.
 */
function clear_failed_logins($conn, $ip_address, $phone_number) {
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ? AND phone_number = ?");
    $stmt->bind_param("ss", $ip_address, $phone_number);
    $stmt->execute();
    $stmt->close();
}

/**
 * CSRF token helpers
 */
function generate_csrf_token($form = 'global') {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (!isset($_SESSION['csrf_tokens'])) $_SESSION['csrf_tokens'] = [];
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][$form] = $token;
    return $token;
}

function csrf_input_field($form = 'global') {
    $token = generate_csrf_token($form);
    return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function validate_csrf_token($token, $form = 'global') {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($token)) return false;
    if (empty($_SESSION['csrf_tokens'][$form])) return false;
    $stored = $_SESSION['csrf_tokens'][$form];
    $valid = hash_equals($stored, $token);
    if ($valid) unset($_SESSION['csrf_tokens'][$form]);
    return $valid;
}

/**
 * Sends an urgent security alert via Telegram.
 * @param mysqli $conn The database connection object.
 */
function send_security_alert_to_telegram($conn, $message) {
    $bot_token = '';
    $chat_id = '';
    
    $stmt = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('telegram_bot_token', 'telegram_alert_chat_id')");
    while ($row = $stmt->fetch_assoc()) {
        if ($row['setting_key'] === 'telegram_bot_token') $bot_token = trim($row['setting_value']);
        if ($row['setting_key'] === 'telegram_alert_chat_id') $chat_id = trim($row['setting_value']);
    }
    
    // Fallback to standard channel if alert chat ID is not set
    if (empty($chat_id)) {
        $stmt_channel = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'telegram_channel_id'");
        if ($row = $stmt_channel->fetch_assoc()) {
            $chat_id = trim($row['setting_value']);
        }
    }

    if (empty($bot_token) || empty($chat_id)) {
        return false;
    }

    $url = "https://api.telegram.org/bot" . $bot_token . "/sendMessage";
    $post_fields = [
        'chat_id' => $chat_id,
        'text' => "🚨 *SECURITY ALERT*\n\n" . $message,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

