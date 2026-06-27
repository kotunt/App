<?php
require_once __DIR__ . '/../core/db_connect.php';
require_once __DIR__ . '/../lang/language.php';

class BetHistoryController {
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
        if (isset($_GET['action']) && $_GET['action'] === 'get_voucher_details') {
            $this->getVoucherDetails();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_bet') {
            $this->cancelBet();
        } else {
            $this->showHistory();
        }
    }

    private function getVoucherDetails() {
        header('Content-Type: application/json');
        $created_at = $_GET['created_at'] ?? '';

        if (empty($created_at)) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            exit();
        }

        $details_stmt = $this->conn->prepare("
            SELECT bet_number, amount, discount_amount, odds, status 
            FROM bets 
            WHERE user_id = ? AND created_at = ? 
            ORDER BY bet_number ASC
        ");
        $details_stmt->bind_param("is", $this->user_id, $created_at);
        $details_stmt->execute();
        $details_result = $details_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $details_stmt->close();

        echo json_encode(['success' => true, 'details' => $details_result]);
        exit();
    }

    private function cancelBet() {
        $voucher_id_to_cancel = $_POST['voucher_id'] ?? '';
        $created_at_to_cancel = $_POST['created_at'] ?? '';
        $error_message = '';
        $success_message = '';

        $cancel_limit_seconds = $this->getBetCancelTimeLimit();

        if (!empty($voucher_id_to_cancel) && !empty($created_at_to_cancel)) {
            $time_diff = time() - strtotime($created_at_to_cancel);
            if ($cancel_limit_seconds > 0 && $time_diff <= $cancel_limit_seconds) {
                $this->conn->begin_transaction();
                try {
                    $voucher_id_like = substr($voucher_id_to_cancel, 0, 8) . '%';
                    $stmt = $this->conn->prepare("SELECT SUM(amount - IFNULL(discount_amount, 0)) as refund_amount, GROUP_CONCAT(id) as bet_ids FROM bets WHERE user_id = ? AND created_at = ? AND status = 'pending' AND MD5(CONCAT(created_at, user_id)) LIKE ? FOR UPDATE");
                    $stmt->bind_param("iss", $this->user_id, $created_at_to_cancel, $voucher_id_like);
                    $stmt->execute();
                    $res = $stmt->get_result()->fetch_assoc();
                    $refund_amount = floatval($res['refund_amount']);
                    $bet_ids_to_delete = $res['bet_ids'];
                    $stmt->close();

                    if ($refund_amount > 0) {
                        // Reverse commissions
                        $this->reverseCommissions($created_at_to_cancel);

                        // Refund user
                        $refund_stmt = $this->conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                        $refund_stmt->bind_param("di", $refund_amount, $this->user_id);
                        $refund_stmt->execute();
                        $refund_stmt->close();

                        // Delete bets
                        if (!empty($bet_ids_to_delete)) {
                            $delete_bets_stmt = $this->conn->prepare("DELETE FROM bets WHERE id IN (" . rtrim(str_repeat('?,', count(explode(',', $bet_ids_to_delete))), ',') . ")");
                            $delete_bets_stmt->bind_param(str_repeat('i', count(explode(',', $bet_ids_to_delete))), ...explode(',', $bet_ids_to_delete));
                            $delete_bets_stmt->execute();
                            $delete_bets_stmt->close();
                        }

                        $this->conn->commit();
                        $success_message = sprintf(__('cancel_bet_success'), number_format($refund_amount));
                    } else {
                        $this->conn->rollback();
                        $error_message = __('cancel_bet_no_record');
                    }
                } catch (Exception $e) {
                    $this->conn->rollback();
                    $error_message = __('system_error_try_again') . " " . $e->getMessage();
                }
            } else {
                 if ($cancel_limit_seconds == 0) {
                    $error_message = __('cancel_bet_disabled');
                } else {
                    $error_message = sprintf(__('cancel_bet_timeout'), $cancel_limit_seconds / 60);
                }
            }
        }
        $this->showHistory(['success_message' => $success_message, 'error_message' => $error_message]);
    }

    private function showHistory($data = []) {
        $filter = $_GET['filter'] ?? 'all';
        $search_number = trim($_GET['search_number'] ?? '');
        $search_date = trim($_GET['search_date'] ?? '');

        // Pagination
        $limit = 10;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? intval($_GET['page']) : 1;
        $offset = ($page - 1) * $limit;

        $filter_sql = "";
        $types = "i";
        $params = [$this->user_id];
        
        // Build filter query
        if ($filter === 'win') $filter_sql .= " AND status = 'win'";
        if ($filter === 'pending') $filter_sql .= " AND status = 'pending'";
        if ($filter === 'lose') $filter_sql .= " AND status = 'lose'";
        if (!empty($search_number)) {
            $filter_sql .= " AND bet_number = ?";
            $types .= "s";
            $params[] = $search_number;
        }
        if (!empty($search_date)) {
            $filter_sql .= " AND DATE(created_at) = ?";
            $types .= "s";
            $params[] = $search_date;
        }

        // Get total rows for pagination
        $count_query = "SELECT COUNT(DISTINCT created_at) as total_rows FROM bets WHERE user_id = ?" . $filter_sql;
        $count_stmt = $this->conn->prepare($count_query);
        $count_stmt->bind_param($types, ...$params);
        $count_stmt->execute();
        $total_rows = $count_stmt->get_result()->fetch_assoc()['total_rows'] ?? 0;
        $count_stmt->close();

        $total_pages = ceil($total_rows / $limit);
        
        // Get bet history data
        $query = "SELECT created_at, COUNT(id) as total_kwek, SUM(amount - IFNULL(discount_amount, 0)) as total_amount, GROUP_CONCAT(bet_number SEPARATOR ', ') as bet_numbers, SUM(CASE WHEN status = 'win' THEN 1 ELSE 0 END) as win_count, SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count, MD5(CONCAT(created_at, user_id)) as voucher_id_hash FROM bets WHERE user_id = ?" . $filter_sql . " GROUP BY created_at ORDER BY created_at DESC LIMIT ?, ?";
        $types .= "ii";
        $params[] = $offset;
        $params[] = $limit;
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $bets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        $summary_data = $this->getDailySummary($search_date);

        $cancel_limit_seconds = $this->getBetCancelTimeLimit();

        $view_data = array_merge($data, compact('bets', 'total_pages', 'page', 'filter', 'search_number', 'search_date', 'summary_data', 'cancel_limit_seconds'));
        
        $this->renderView('bet_history_view', $view_data);
    }

    private function getDailySummary($search_date) {
        $summary_date = !empty($search_date) ? $search_date : date('Y-m-d');
        $summary_stmt = $this->conn->prepare("
            SELECT 
                COUNT(id) as total_tickets,
                SUM(amount - IFNULL(discount_amount, 0)) as total_bet_amount,
                SUM(CASE WHEN status = 'win' THEN 1 ELSE 0 END) as win_tickets,
                SUM(CASE WHEN status = 'lose' THEN 1 ELSE 0 END) as lose_tickets,
                SUM(CASE WHEN status = 'win' AND LENGTH(bet_number) = 2 THEN amount * IFNULL(odds, 80) 
                         WHEN status = 'win' AND LENGTH(bet_number) = 3 THEN amount * IFNULL(odds, 500) 
                         ELSE 0 END) as total_win
            FROM bets 
            WHERE user_id = ? AND DATE(created_at) = ?
        ");
        $summary_stmt->bind_param("is", $this->user_id, $summary_date);
        $summary_stmt->execute();
        $res = $summary_stmt->get_result()->fetch_assoc();
        $summary_stmt->close();

        return [
            'summary_date' => $summary_date,
            'daily_bet' => floatval($res['total_bet_amount'] ?? 0),
            'daily_win' => floatval($res['total_win'] ?? 0),
            'daily_profit' => floatval($res['total_win'] ?? 0) - floatval($res['total_bet_amount'] ?? 0),
            'daily_total_tickets' => intval($res['total_tickets'] ?? 0),
            'daily_win_tickets' => intval($res['win_tickets'] ?? 0),
            'daily_lose_tickets' => intval($res['lose_tickets'] ?? 0)
        ];
    }
    
    private function reverseCommissions($created_at_to_cancel) {
        $comm_stmt = $this->conn->prepare("SELECT id, referrer_id, amount FROM commissions WHERE referred_user_id = ? AND created_at >= ? - INTERVAL 2 SECOND AND created_at <= ? + INTERVAL 2 SECOND FOR UPDATE");
        $comm_stmt->bind_param("iss", $this->user_id, $created_at_to_cancel, $created_at_to_cancel);
        $comm_stmt->execute();
        $comm_res = $comm_stmt->get_result();

        $update_user_balance_stmt = $this->conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $delete_commission_stmt = $this->conn->prepare("DELETE FROM commissions WHERE id = ?");

        while ($comm = $comm_res->fetch_assoc()) {
            $update_user_balance_stmt->bind_param("di", $comm['amount'], $comm['referrer_id']);
            $update_user_balance_stmt->execute();
            
            $delete_commission_stmt->bind_param("i", $comm['id']);
            $delete_commission_stmt->execute();
        }
        $update_user_balance_stmt->close();
        $delete_commission_stmt->close();
        $comm_stmt->close();
    }

    private function getBetCancelTimeLimit() {
        $setting_stmt = $this->conn->query("SELECT setting_value FROM settings WHERE setting_key = 'bet_cancel_time_limit'");
        $setting_row = $setting_stmt->fetch_assoc();
        $cancel_limit_minutes = $setting_row ? intval($setting_row['setting_value']) : 10;
        return $cancel_limit_minutes * 60;
    }

    private function renderView($viewName, $data = []) {
        extract($data);
        $page_title = __('title_bet_history') . " - Thai 2D3D";
        require_once __DIR__ . '/../views/' . $viewName . '.php';
    }
}
