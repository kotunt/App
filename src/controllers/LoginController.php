<?php

class LoginController {
    private $db;
    private $error_message = "";

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php");
            exit();
        }
        
        require_once __DIR__ . '/../core/db_connect.php';
        require_once __DIR__ . '/../core/security_helper.php';
        require_once __DIR__ . '/../lang/language.php';

        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            error_log("Login page critical error: " . $e->getMessage());
            $this->error_message = __('system_error_try_again');
        }
    }

    public function handleRequest() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->processLogin();
        } else {
            $this->showLoginPage();
        }
    }

    private function showLoginPage() {
        if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
            $this->error_message = __('session_timeout_error');
        } elseif (isset($_GET['banned']) && $_GET['banned'] == 1) {
            $this->error_message = __('account_banned');
        }

        $page_title = __('login_page_title');
        $error_message = $this->error_message;
        require_once __DIR__ . '/../views/login_view.php';
    }

    private function processLogin() {
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';
        $ip_address = $_SERVER['REMOTE_ADDR'];

        if (empty($phone) || empty($password)) {
            $this->error_message = __('fill_all_fields');
        } elseif (!$this->db) {
            // Error message is already set in constructor
        } else {
            if (!check_login_rate_limit($this->db, $ip_address, $phone)) {
                $this->error_message = __('login_rate_limit_error');
                send_security_alert_to_telegram($this->db, "Multiple failed login attempts detected.
IP: `{$ip_address}`
Phone: `{$phone}`");
            } else {
                $this->authenticateUser($phone, $password, $ip_address);
            }
        }
        
        if (!empty($this->error_message)) {
            $page_title = __('login_page_title');
            $error_message = $this->error_message;
            require_once __DIR__ . '/../views/login_view.php';
        }
    }

    private function authenticateUser($phone, $password, $ip_address) {
        $stmt = $this->db->prepare("SELECT id, username, password, is_banned, verification_status, role, google2fa_secret, last_login_ip FROM users WHERE phone_number = ?");
        
        if (!$stmt) {
            error_log("Login page database error: " . $this->db->error);
            $this->error_message = __('system_error_try_again');
            return;
        }

        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $this->handleSuccessfulPasswordVerification($user, $ip_address);
            } else {
                $this->error_message = __('incorrect_password');
                record_failed_login($this->db, $ip_address, $phone);
            }
        } else {
            $this->error_message = __('phone_not_found');
            record_failed_login($this->db, $ip_address, $phone);
        }
        $stmt->close();
    }

    private function handleSuccessfulPasswordVerification($user, $ip_address) {
        if (isset($user['verification_status']) && $user['verification_status'] === 'pending') {
            $this->error_message = __('account_pending_verification');
            return;
        }
        if (isset($user['verification_status']) && $user['verification_status'] === 'rejected') {
            $this->error_message = __('account_rejected');
            return;
        }
        if ($user['is_banned']) {
            $this->error_message = __('account_banned');
            return;
        }

        // Maintenance Mode Check
        $m_stmt = $this->db->query("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'");
        $m_mode = ($m_stmt && $m_row = $m_stmt->fetch_assoc()) ? $m_row['setting_value'] : '0';
        if ($m_mode === '1' && !in_array($user['role'], ['admin', 'sub_admin'])) {
            $this->error_message = __('maintenance_mode_error');
            return;
        }
        
        clear_failed_logins($this->db, $ip_address, $user['phone_number'] ?? ''); // Assuming phone is available
        $this->updateLastLoginIp($user, $ip_address);

        if (!empty($user['google2fa_secret'])) {
            $this->setup2FASession($user);
            header("Location: verify_2fa.php");
            exit();
        } else {
            $this->establishSession($user);
            header("Location: index.php");
            exit();
        }
    }

    private function updateLastLoginIp($user, $ip_address) {
        $check_ip_column = $this->db->query("SHOW COLUMNS FROM users LIKE 'last_login_ip'");
        if ($check_ip_column && $check_ip_column->num_rows > 0) {
            if (in_array($user['role'], ['admin', 'sub_admin']) && $user['last_login_ip'] !== $ip_address && !empty($user['last_login_ip'])) {
                send_security_alert_to_telegram($this->db, "Admin Login from a NEW IP address!
Admin: `{$user['username']}`
New IP: `{$ip_address}`
Old IP: `{$user['last_login_ip']}`");
            }
            $ip_stmt = $this->db->prepare("UPDATE users SET last_login_ip = ? WHERE id = ?");
            if ($ip_stmt) {
                $ip_stmt->bind_param("si", $ip_address, $user['id']);
                $ip_stmt->execute();
            }
        }
    }

    private function setup2FASession($user) {
        $_SESSION['temp_2fa_user_id'] = $user['id'];
        $_SESSION['temp_2fa_username'] = $user['username'];
        $_SESSION['temp_2fa_phone'] = $_POST['phone'] ?? ''; // Assuming phone is from POST
        $_SESSION['temp_2fa_role'] = $user['role'];
    }

    private function establishSession($user) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();
        unset($_SESSION['permissions']);
    }
}
