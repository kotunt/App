<?php

class ForgotPasswordController {
    private $db;
    private $step = 1;
    private $error_message = "";
    private $success_message = "";

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        require_once __DIR__ . '/../core/db_connect.php';
        require_once __DIR__ . '/../core/security_helper.php';
        require_once __DIR__ . '/../lang/language.php';

        try {
            $this->db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            error_log("Forgot Password page critical error: " . $e->getMessage());
            $this->error_message = __('system_error_try_again');
        }
    }

    public function handleRequest() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['verify'])) {
                $this->handleStep1_VerifyUser();
            } elseif (isset($_POST['verify_otp'])) {
                $this->handleStep2_VerifyOTP();
            } elseif (isset($_POST['reset'])) {
                $this->handleStep3_ResetPassword();
            }
        }
        
        // If the session holds a step, use it.
        // This is to keep the user on the right step on page refresh.
        if (isset($_SESSION['reset_step'])) {
            $this->step = $_SESSION['reset_step'];
        }

        $this->loadView();
    }

    private function handleStep1_VerifyUser() {
        if (!$this->db) { $this->loadView(); return; }
        
        $phone = trim($_POST['phone'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $conn = $this->db; // for consistency with original code's variable name

        if (!check_rate_limit($ip_address, 'forgot_password', 5, 600)) {
            $this->error_message = __('rate_limit_exceeded');
            send_security_alert_to_telegram($conn, "Forgot Password rate limit exceeded for IP: `{$ip_address}`");
            return;
        }

        if (empty($phone) || empty($username)) {
            $this->error_message = __('fill_all_fields_forgot_pw');
            return;
        }

        $stmt = $conn->prepare("SELECT id, telegram_chat_id FROM users WHERE phone_number = ? AND username = ?");
        $stmt->bind_param("ss", $phone, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            $_SESSION['reset_user_id'] = $user['id'];
            
            $bot_token = $this->getTelegramBotToken();
            
            if (!empty($user['telegram_chat_id']) && !empty($bot_token)) {
                $this->sendOtpViaTelegram($user['telegram_chat_id'], $bot_token);
                $this->step = 2;
                $_SESSION['reset_step'] = 2;
                $this->success_message = __('forgot_pw_otp_sent_telegram');
            } else {
                $this->error_message = __('forgot_pw_no_telegram');
                unset($_SESSION['reset_user_id']);
                $this->step = 1;
            }
        } else {
            record_failed_attempt($ip_address, 'forgot_password');
            $this->error_message = __('forgot_pw_user_not_found');
        }
        $stmt->close();
    }

    private function handleStep2_VerifyOTP() {
        $this->step = 2; // Keep on step 2 in case of error
        $_SESSION['reset_step'] = 2;

        $entered_otp = trim($_POST['otp'] ?? '');
        if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_otp_expiry']) || !isset($_SESSION['reset_user_id'])) {
            $this->error_message = __('session_expired_try_again');
            $this->resetFlow();
            return;
        }
        
        if (time() > $_SESSION['reset_otp_expiry']) {
            $this->error_message = __('otp_expired_error');
            $this->resetFlow();
        } elseif ($entered_otp === $_SESSION['reset_otp']) {
            $this->success_message = __('otp_verified_success');
            $this->step = 3;
            $_SESSION['reset_step'] = 3;
            unset($_SESSION['reset_otp'], $_SESSION['reset_otp_expiry']);
        } else {
            $this->error_message = __('otp_invalid_error');
            record_failed_attempt($_SESSION['reset_user_id'], 'otp_verify');
        }
    }

    private function handleStep3_ResetPassword() {
        $this->step = 3; // Keep on step 3 in case of error
        $_SESSION['reset_step'] = 3;

        if (!isset($_SESSION['reset_user_id'])) {
            $this->error_message = __('session_expired_try_again');
            $this->resetFlow();
            return;
        }

        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (strlen($new_password) < 6) {
            $this->error_message = __('new_password_min_length');
        } elseif ($new_password !== $confirm_password) {
            $this->error_message = __('new_password_mismatch');
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['reset_user_id']);
            
            if ($stmt->execute()) {
                $this->success_message = __('password_reset_success');
                $this->step = 4;
                unset($_SESSION['reset_user_id'], $_SESSION['reset_step']);
            } else {
                $this->error_message = __('password_reset_error');
            }
            $stmt->close();
        }
    }
    
    private function getTelegramBotToken() {
        $bot_token = '';
        $tg_set_stmt = $this->db->query("SELECT setting_value FROM settings WHERE setting_key = 'telegram_bot_token'");
        if ($tg_set_stmt && $tg_set_stmt->num_rows > 0) {
            $bot_token = $tg_set_stmt->fetch_assoc()['setting_value'] ?? '';
        }
        return $bot_token;
    }

    private function sendOtpViaTelegram($chat_id, $bot_token) {
        $otp = rand(100000, 999999);
        $_SESSION['reset_otp'] = (string)$otp;
        $_SESSION['reset_otp_expiry'] = time() + 300; // 5 minutes validity
        
        $telegram_msg = str_replace('{otp}', $otp, __('telegram_otp_message_forgot_pw'));
        $telegram_url = "https://api.telegram.org/bot" . $bot_token . "/sendMessage";
        $telegram_data = [
            'chat_id' => $chat_id,
            'text' => $telegram_msg,
            'parse_mode' => 'Markdown'
        ];

        $ch = curl_init($telegram_url);
        curl_setopt_array($ch, [CURLOPT_URL => $telegram_url, CURLOPT_POST => TRUE, CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_TIMEOUT => 3, CURLOPT_POSTFIELDS => http_build_query($telegram_data)]);
        curl_exec($ch);
        curl_close($ch);
    }
    
    private function resetFlow() {
        $this->step = 1;
        unset($_SESSION['reset_user_id'], $_SESSION['reset_otp'], $_SESSION['reset_otp_expiry'], $_SESSION['reset_step']);
    }

    private function loadView() {
        $page_title = __('forgot_password_page_title');
        $step = $this->step;
        $error_message = $this->error_message;
        $success_message = $this->success_message;
        
        // If a user navigates back to the root of the page, reset the flow.
        if ($_SERVER["REQUEST_METHOD"] != "POST" && $step > 1) {
            $this->resetFlow();
            // Re-assign variables for the view after reset
            $step = $this->step;
            $error_message = __('session_expired_try_again');
        }

        require_once __DIR__ . '/../views/forgot_password_view.php';
    }
}
