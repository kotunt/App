<?php

class ProfileController {
    private $conn;
    private $user_id;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        $this->user_id = $_SESSION['user_id'];
        require_once __DIR__ . '/../../core/db_connect.php';
        require_once __DIR__ . '/../../lang/language.php';
        $this->conn = $conn;
    }

    public function handleRequest() {
        $data = [];
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $data = $this->handlePostRequest();
        }
        
        $page_data = $this->preparePageData();
        
        // Merge POST results (success/error messages) with page data
        $view_data = array_merge($page_data, $data);

        $this->renderView($view_data);
    }

    private function handlePostRequest() {
        $error_message = "";
        $success_message = "";

        $username = trim($_POST['username'] ?? '');
        $telegram_chat_id = trim($_POST['telegram_chat_id'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');

        if (empty($username) || empty($phone_number)) {
            $error_message .= __('name_phone_required');
        } else {
            $check_stmt = $this->conn->prepare("SELECT id FROM users WHERE phone_number = ? AND id != ?");
            $check_stmt->bind_param("si", $phone_number, $this->user_id);
            $check_stmt->execute();
            $check_stmt->store_result();
            
            if ($check_stmt->num_rows > 0) {
                $error_message .= __('phone_already_used');
            }
            $check_stmt->close();

            if (empty($error_message)) {
                $stmt = $this->conn->prepare("UPDATE users SET username = ?, telegram_chat_id = ?, phone_number = ? WHERE id = ?");
                $stmt->bind_param("sssi", $username, $telegram_chat_id, $phone_number, $this->user_id);

                if ($stmt->execute()) {
                    $_SESSION['username'] = $username;
                    $success_message .= __('profile_updated_successfully');
                    
                    $telegram_result = $this->sendTelegramTestMessage($username, $telegram_chat_id);
                    if ($telegram_result !== null) {
                        $success_message .= $telegram_result['success'] ?? '';
                        $error_message .= $telegram_result['error'] ?? '';
                    }
                } else {
                    $error_message .= __('update_error');
                }
                $stmt->close();
            }
        }
        
        return [
            'success_message' => $success_message,
            'error_message' => $error_message,
        ];
    }
    
    private function sendTelegramTestMessage($username, $telegram_chat_id) {
        if (empty($telegram_chat_id)) return null;

        $tg_stmt = $this->conn->query("SELECT setting_value FROM settings WHERE setting_key = 'telegram_bot_token'");
        $bot_token = $tg_stmt->fetch_assoc()['setting_value'] ?? '';

        if (empty($bot_token)) return null;

        $telegram_msg = str_replace('{username}', $username, __('telegram_test_message_text'));
        $telegram_url = "https://api.telegram.org/bot" . $bot_token . "/sendMessage";
        $telegram_data = [
            'chat_id' => $telegram_chat_id, 'text' => $telegram_msg, 'parse_mode' => 'Markdown'
        ];

        $ch = curl_init($telegram_url);
        curl_setopt_array($ch, [CURLOPT_URL => $telegram_url, CURLOPT_POST => TRUE, CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_TIMEOUT => 3, CURLOPT_POSTFIELDS => http_build_query($telegram_data)]);
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code == 200) {
            return ['success' => "<br><span class='block mt-2'><i class='fab fa-telegram mr-1'></i> " . __('telegram_test_message_sent') . "</span>"];
        } else {
            return ['error' => "<br><span class='block mt-2'><i class='fas fa-exclamation-triangle mr-1'></i> " . __('telegram_test_message_failed') . "</span>"];
        }
    }

    private function preparePageData() {
        $stmt = $this->conn->prepare("SELECT username, phone_number, referral_code, avatar, kbz_pay_number, wave_pay_number, vip_level, telegram_chat_id FROM users WHERE id = ?");
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $user_data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $lifetime_stats = $this->getLifetimeStats();
        $vip_data = $this->getVipProgress($user_data['vip_level'], $lifetime_stats['lifetime_bet']);

        return [
            'user_data' => $user_data,
            'lifetime_bet' => $lifetime_stats['lifetime_bet'],
            'lifetime_win' => $lifetime_stats['lifetime_win'],
            'lifetime_profit' => $lifetime_stats['lifetime_profit'],
            'vip_thresholds' => $vip_data['vip_thresholds'],
            'next_level' => $vip_data['next_level'],
            'next_threshold' => $vip_data['next_threshold'],
            'current_level' => $vip_data['current_level'],
            'progress_percent' => $vip_data['progress_percent'],
        ];
    }

    private function getLifetimeStats() {
        $lifetime_stmt = $this->conn->prepare("
            SELECT 
                SUM(amount - IFNULL(discount_amount, 0)) as total_bet,
                SUM(CASE WHEN status = 'win' AND LENGTH(bet_number) = 2 THEN amount * IFNULL(odds, 80) 
                         WHEN status = 'win' AND LENGTH(bet_number) = 3 THEN amount * IFNULL(odds, 500) 
                         ELSE 0 END) as total_win
            FROM bets 
            WHERE user_id = ?
        ");
        $lifetime_stmt->bind_param("i", $this->user_id);
        $lifetime_stmt->execute();
        $lifetime_res = $lifetime_stmt->get_result()->fetch_assoc();
        $lifetime_stmt->close();

        $lifetime_bet = floatval($lifetime_res['total_bet'] ?? 0);
        $lifetime_win = floatval($lifetime_res['total_win'] ?? 0);
        
        return [
            'lifetime_bet' => $lifetime_bet,
            'lifetime_win' => $lifetime_win,
            'lifetime_profit' => $lifetime_win - $lifetime_bet,
        ];
    }
    
    private function getVipProgress($current_level, $lifetime_bet) {
        $vip_settings_stmt = $this->conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'vip_%'");
        $vip_thresholds = [];
        while ($row = $vip_settings_stmt->fetch_assoc()) {
            $vip_thresholds[$row['setting_key']] = floatval($row['setting_value']);
        }

        $next_level = 'Bronze';
        $next_threshold = $vip_thresholds['vip_bronze_threshold'] ?? 100000;

        if ($current_level === 'Bronze') {
            $next_level = 'Silver';
            $next_threshold = $vip_thresholds['vip_silver_threshold'] ?? 500000;
        } elseif ($current_level === 'Silver') {
            $next_level = 'Gold';
            $next_threshold = $vip_thresholds['vip_gold_threshold'] ?? 2000000;
        } elseif ($current_level === 'Gold') {
            $next_level = 'Diamond';
            $next_threshold = $vip_thresholds['vip_diamond_threshold'] ?? 5000000;
        } elseif ($current_level === 'Diamond') {
            $next_level = 'Max';
            $next_threshold = $lifetime_bet;
        }
        
        $progress_percent = ($next_threshold > 0) ? min(100, ($lifetime_bet / $next_threshold) * 100) : 0;
        if ($next_level === 'Max') $progress_percent = 100;
        
        return [
            'vip_thresholds' => $vip_thresholds,
            'next_level' => $next_level,
            'next_threshold' => $next_threshold,
            'current_level' => $current_level,
            'progress_percent' => $progress_percent,
        ];
    }

    private function renderView($data) {
        // Extract data for easy access in the view
        extract($data);
        $page_title = __('edit_profile') . " - Thai 2D3D";
        require_once __DIR__ . '/../../views/profile_view.php';
    }
}
