<?php
require_once __DIR__ . '/../core/db_connect.php';
require_once __DIR__ . '/../core/functions.php';
require_once __DIR__ . '/../lang/language.php';

class DepositController {
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
        $this->conn = (new Database())->getConnection();
    }

    public function handleRequest() {
        $action = $_POST['action'] ?? null;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                $this->renderView('deposit_view', ['error_message' => 'Invalid CSRF token.', 'step' => 1]);
                return;
            }

            if ($action === 'step1') {
                $this->handleStep1();
            } elseif ($action === 'step2') {
                $this->handleStep2();
            }
        } else {
            // Not a post request, determine which step to show
            $step = $_SESSION['deposit_step'] ?? 1;
             if ($step == 2 && !isset($_SESSION['deposit_data'])) {
                $step = 1; // If session data is lost, go back to step 1
                $_SESSION['deposit_step'] = $step;
            }
            $this->renderView('deposit_view', ['step' => $step]);
        }
    }

    private function handleStep1() {
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
        $payment_method = trim($_POST['payment_method'] ?? '');

        $settings = $this->getPaymentSettings();
        $min_deposit = $settings['min_deposit'];
        $max_deposit = $settings['max_deposit'];

        if ($amount === false || $amount < $min_deposit || $amount > $max_deposit) {
            $error_message = __('deposit_amount_must_be_between') . number_format($min_deposit) . ' ' . __('and') . ' ' . number_format($max_deposit);
            $this->renderView('deposit_view', ['error_message' => $error_message, 'step' => 1, 'submitted_data' => $_POST]);
        } elseif (empty($payment_method)) {
            $error_message = __('please_select_payment_method');
            $this->renderView('deposit_view', ['error_message' => $error_message, 'step' => 1, 'submitted_data' => $_POST]);
        } else {
            $_SESSION['deposit_data'] = [
                'amount' => $amount,
                'payment_method' => $payment_method
            ];
            $_SESSION['deposit_step'] = 2;
            header("Location: deposit.php");
            exit();
        }
    }

    private function handleStep2() {
        if (isset($_POST['back'])) {
            $_SESSION['deposit_step'] = 1;
            // Keep deposit_data for pre-filling form
            header("Location: deposit.php");
            exit();
        }

        if (!isset($_SESSION['deposit_data'])) {
            $this->renderView('deposit_view', ['error_message' => __('session_expired_error'), 'step' => 1]);
            return;
        }

        $amount = $_SESSION['deposit_data']['amount'];
        $payment_method = $_SESSION['deposit_data']['payment_method'];
        $transaction_id = trim($_POST['transaction_id'] ?? '');

        if (empty($transaction_id)) {
            $this->renderView('deposit_view', ['error_message' => __('transaction_id_required'), 'step' => 2]);
            return;
        }

        $slip_image_url = $this->uploadSlipImage();

        if (empty($slip_image_url)) {
            $this->renderView('deposit_view', ['error_message' => __('slip_upload_error'), 'step' => 2]);
            return;
        }

        $settings = $this->getPaymentSettings();
        
        // Auto-Approve check
        $auto_approve_result = $this->checkAutoApproval($payment_method, $transaction_id, $amount);
        $is_auto_approved = $auto_approve_result['approved'];
        $pre_approved_id = $auto_approve_result['id'];

        $initial_status = $is_auto_approved ? 'approved' : 'pending';
        
        $stmt = $this->conn->prepare("INSERT INTO deposits (user_id, amount, payment_method, transaction_id, slip_image_url, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("idssss", $this->user_id, $amount, $payment_method, $transaction_id, $slip_image_url, $initial_status);

        if ($stmt->execute()) {
            if ($is_auto_approved) {
                $this->processAutoApproval($pre_approved_id, $amount);
                $_SESSION['deposit_success_message'] = __('deposit_auto_approved_success');
            } else {
                $this->notifyAdmin($amount, $payment_method, $transaction_id, $settings);
                $_SESSION['deposit_success_message'] = __('deposit_request_successful');
            }
            $_SESSION['deposit_step'] = 3;
            unset($_SESSION['deposit_data']);
            header("Location: deposit.php");
            exit();
        } else {
            $this->renderView('deposit_view', ['error_message' => __('system_error_try_again'), 'step' => 2]);
        }
    }
    
    private function uploadSlipImage() {
        if (isset($_FILES['slip_image']) && $_FILES['slip_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../uploads/slips/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['slip_image']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array($ext, $allowed_exts)) {
                $new_filename = 'slip_' . $this->user_id . '_' . time() . '_' . uniqid() . '.' . $ext;
                $upload_path = $upload_dir . $new_filename;
                require_once __DIR__ . '/../core/image_helper.php';
                if (compressImage($_FILES['slip_image']['tmp_name'], $upload_path, 60)) {
                    return 'uploads/slips/' . $new_filename;
                }
            }
        }
        return null;
    }

    private function checkAutoApproval($payment_method, $transaction_id, $amount) {
        $stmt = $this->conn->prepare("SELECT id, amount FROM pre_approved_transactions WHERE payment_method = ? AND transaction_id = ? AND status = 'pending'");
        $stmt->bind_param("ss", $payment_method, $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (abs(floatval($row['amount']) - $amount) < 0.01) { // Use tolerance for float comparison
                return ['approved' => true, 'id' => $row['id']];
            }
        }
        return ['approved' => false, 'id' => 0];
    }

    private function processAutoApproval($pre_approved_id, $amount) {
        $this->conn->begin_transaction();
        try {
            $upd_pre = $this->conn->prepare("UPDATE pre_approved_transactions SET status = 'used' WHERE id = ?");
            $upd_pre->bind_param("i", $pre_approved_id);
            $upd_pre->execute();

            $upd_user = $this->conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $upd_user->bind_param("di", $amount, $this->user_id);
            $upd_user->execute();
            
            $noti_msg = str_replace('%amount%', number_format($amount), __('deposit_auto_approved_noti'));
            $stmt_noti = $this->conn->prepare("INSERT INTO system_notifications (user_id, message) VALUES (?, ?)");
            $stmt_noti->bind_param("is", $this->user_id, $noti_msg);
            $stmt_noti->execute();

            $stmt_noti_update = $this->conn->prepare("UPDATE users SET notifications = notifications + 1 WHERE id = ?");
            $stmt_noti_update->bind_param("i", $this->user_id);
            $stmt_noti_update->execute();

            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
            // Log error, maybe revert deposit to pending
        }
    }

    private function notifyAdmin($amount, $payment_method, $transaction_id, $settings) {
        $bot_token = $settings['telegram_bot_token'] ?? '';
        $admin_chat_id = $settings['telegram_channel_id'] ?? '';
        if (!empty($bot_token) && !empty($admin_chat_id)) {
            $telegram_msg = __('admin_deposit_noti_title') . "\n\n" .
                            __('admin_deposit_noti_user_id') . " `" . $this->user_id . "`\n" .
                            __('admin_deposit_noti_amount') . " *" . number_format($amount) . "* " . __('currency') . "\n" .
                            __('admin_deposit_noti_method') . " " . htmlspecialchars($payment_method) . "\n" .
                            __('admin_deposit_noti_trx_id') . " `" . htmlspecialchars($transaction_id) . "`\n" .
                            __('admin_deposit_noti_time') . " " . date('Y-m-d h:i:s A') . "\n" .
                            __('admin_deposit_noti_slip_info');
            
            $telegram_url = "https://api.telegram.org/bot" . $bot_token . "/sendMessage";
            $post_fields = http_build_query(['chat_id' => $admin_chat_id, 'text' => $telegram_msg, 'parse_mode' => 'Markdown']);
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $telegram_url,
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_TIMEOUT_MS => 1500,
                CURLOPT_NOSIGNAL => 1,
                CURLOPT_POSTFIELDS => $post_fields
            ]);
            curl_exec($ch);
            curl_close($ch);
        }
    }

    private function getPaymentSettings() {
        $settings_query = $this->conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('min_deposit', 'max_deposit', 'telegram_bot_token', 'telegram_channel_id')");
        $pay_settings = [];
        while ($row = $settings_query->fetch_assoc()) {
            $pay_settings[$row['setting_key']] = $row['setting_value'];
        }
        $pay_settings['min_deposit'] = isset($pay_settings['min_deposit']) ? floatval($pay_settings['min_deposit']) : 1000;
        $pay_settings['max_deposit'] = isset($pay_settings['max_deposit']) ? floatval($pay_settings['max_deposit']) : 1000000;
        return $pay_settings;
    }

    private function getPaymentAccounts() {
        $acc_stmt = $this->conn->query("SELECT * FROM payment_accounts WHERE is_active = 1 ORDER BY sort_order ASC");
        return $acc_stmt ? $acc_stmt->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function renderView($viewName, $data = []) {
        extract($data);
        
        $page_title = __('title_deposit') . " - Thai 2D3D";
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $settings = $this->getPaymentSettings();
        $min_deposit = $settings['min_deposit'];
        $max_deposit = $settings['max_deposit'];
        $payment_accounts = $this->getPaymentAccounts();

        // Handle step 3 success message display
        if (isset($_SESSION['deposit_step']) && $_SESSION['deposit_step'] === 3) {
             $step = 3;
             $success_message = $_SESSION['deposit_success_message'] ?? '';
             unset($_SESSION['deposit_step'], $_SESSION['deposit_success_message'], $_SESSION['deposit_data']);
        }
        
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}
