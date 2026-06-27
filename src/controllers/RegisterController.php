<?php

class RegisterController {
    private $db;
    private $error_message = "";
    private $success_message = "";
    
    // To hold user input
    private $username_value = "";
    private $phone_value = "";
    private $referral_code_value = "";

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            header("Location: index.php");
            exit();
        }

        require_once __DIR__ . '/../core/db_connect.php';
        require_once __DIR__ . '/../core/security_helper.php';
        require_once __DIR__ . '/../lang/language.php';

        try {
            // In register.php it was $conn, so we will use the same for consistency inside the methods
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            error_log("Register page critical error: " . $e->getMessage());
            $this->error_message = __('system_error_try_again');
        }
    }

    public function handleRequest() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->processRegistration();
        } else {
            $this->showRegistrationPage();
        }
    }

    private function showRegistrationPage() {
        $page_title = __('register_page_title');
        
        // Pass GET referral code to the view
        $this->referral_code_value = $_GET['ref'] ?? '';

        $this->loadView();
    }

    private function processRegistration() {
        if (!$this->db) {
            $this->loadView();
            return;
        }

        $this->username_value = trim($_POST['username'] ?? '');
        $this->phone_value = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $this->referral_code_value = trim($_POST['referral_code'] ?? '');
        
        $ip_address = $_SERVER['REMOTE_ADDR'];

        if ($this->isRateLimited($ip_address)) {
            $this->error_message = __('spam_registration_error');
        } elseif ($this->validateInput()) {
            $this->registerUser($password, $ip_address);
        }
        
        $this->loadView();
    }
    
    private function validateInput() {
        if (empty($this->username_value) || empty($this->phone_value) || empty($_POST['password'])) {
            $this->error_message = __('register_empty_fields');
            return false;
        }
        if (!preg_match('/^[0-9]{9,15}$/', $this->phone_value)) {
            $this->error_message = __('invalid_phone_format');
            return false;
        }
        if (strlen($_POST['password']) < 6) {
            $this->error_message = __('password_length_error');
            return false;
        }
        if ($this->isPhoneRegistered($this->phone_value)) {
            $this->error_message = __('phone_already_registered');
            return false;
        }
        return true;
    }

    private function isRateLimited($ip_address) {
        $time_limit = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $spam_stmt = $this->db->prepare("SELECT COUNT(*) as attempts FROM registration_attempts WHERE ip_address = ? AND created_at > ?");
        $spam_stmt->bind_param("ss", $ip_address, $time_limit);
        $spam_stmt->execute();
        $spam_result = $spam_stmt->get_result()->fetch_assoc();
        $spam_stmt->close();
        return $spam_result['attempts'] >= 5;
    }

    private function isPhoneRegistered($phone) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE phone_number = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->store_result();
        $is_registered = $stmt->num_rows > 0;
        $stmt->close();
        return $is_registered;
    }
    
    private function getReferrerId($referral_code) {
        if (empty($referral_code)) {
            return null;
        }
        
        $ref_stmt = $this->db->prepare("SELECT id FROM users WHERE referral_code = ?");
        $ref_stmt->bind_param("s", $referral_code);
        $ref_stmt->execute();
        $ref_result = $ref_stmt->get_result();
        if ($ref_row = $ref_result->fetch_assoc()) {
            return $ref_row['id'];
        }
        
        $this->error_message = __('invalid_referral_code');
        return false; // Return false to indicate error
    }

    private function registerUser($password, $ip_address) {
        $conn = $this->db;

        $referred_by = $this->getReferrerId($this->referral_code_value);
        if ($referred_by === false) {
            // Error message is already set in getReferrerId
            return;
        }

        // Generate a unique 6-character referral code
        $new_referral_code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert_stmt = $conn->prepare("INSERT INTO users (username, phone_number, password, referral_code, referred_by, last_login_ip) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("ssssis", $this->username_value, $this->phone_value, $hashed_password, $new_referral_code, $referred_by, $ip_address);

        if ($insert_stmt->execute()) {
            $this->logRegistrationAttempt($ip_address);
            $this->success_message = __('register_success');
            // Clear input fields on success
            $this->username_value = "";
            $this->phone_value = "";
            $this->referral_code_value = "";
        } else {
            $this->error_message = __('system_error_try_again');
            error_log("User registration failed: " . $insert_stmt->error);
        }
        $insert_stmt->close();
    }

    private function logRegistrationAttempt($ip_address) {
        $log_stmt = $this->db->prepare("INSERT INTO registration_attempts (ip_address) VALUES (?)");
        $log_stmt->bind_param("s", $ip_address);
        $log_stmt->execute();
        $log_stmt->close();
    }

    private function loadView() {
        // Expose variables to the view
        $page_title = __('register_page_title');
        $error_message = $this->error_message;
        $success_message = $this->success_message;
        
        $username_value = $this->username_value;
        $phone_value = $this->phone_value;
        $referral_code_value = $this->referral_code_value;

        require_once __DIR__ . '/../views/register_view.php';
    }
}
