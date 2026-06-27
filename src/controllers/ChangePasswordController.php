<?php

class ChangePasswordController {
    private $db;
    private $user_id;
    private $error_message = "";
    private $success_message = "";

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Authentication check
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }
        $this->user_id = $_SESSION['user_id'];

        require_once __DIR__ . '/../core/db_connect.php';
        require_once __DIR__ . '/../lang/language.php';

        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            error_log("Change Password page critical error: " . $e->getMessage());
            $this->error_message = __('system_error_try_again');
        }

        // CSRF Token generation
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public function handleRequest() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->processPasswordChange();
        }
        $this->loadView();
    }

    private function processPasswordChange() {
        if (!$this->db) { return; }

        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $this->error_message = __('csrf_token_mismatch');
            return;
        }
        
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($new_password) || empty($confirm_password)) {
            $this->error_message = __('fill_new_password_completely');
        } elseif (strlen($new_password) < 6) {
            $this->error_message = __('new_password_min_length');
        } elseif ($new_password !== $confirm_password) {
            $this->error_message = __('new_password_mismatch');
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $this->user_id);
            if ($stmt->execute()) {
                $this->success_message = __('password_changed_successfully');
            } else {
                $this->error_message = __('update_error');
                error_log("Password change failed for user_id: {$this->user_id}. DB error: " . $stmt->error);
            }
            $stmt->close();
        }
    }

    private function loadView() {
        $page_title = __('change_password_page_title');
        $error_message = $this->error_message;
        $success_message = $this->success_message;
        $csrf_token = $_SESSION['csrf_token'];

        require_once __DIR__ . '/../views/change_password_view.php';
    }
}
