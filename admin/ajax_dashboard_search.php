<?php
session_start();
require_once __DIR__ . '/../core/db_connect.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Admin များသာ ဝင်ရောက်ခွင့်ရှိသည်
require_admin_login();

// CSRF Token Check for AJAX
$csrf_token_header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($csrf_token_header) || !isset($_SESSION['admin_csrf_token']) || !hash_equals($_SESSION['admin_csrf_token'], $csrf_token_header)) {
    http_response_code(403);
    die(json_encode(['error' => 'Invalid security token.']));
}

/**
 * Fetches recent transactions with an optional search term.
 * @param mysqli $conn The database connection.
 * @param string $search_term The term to search for.
 * @return array The list of recent transactions.
 */
function get_recent_transactions($conn, $search_term = '') {
    $tx_cond_d = "1=1";
    $tx_cond_w = "1=1";
    $tx_types = '';
    $params = [];
    if (!empty($search_term)) {
        $like_term = "%" . $search_term . "%";
        $tx_cond_d = "(u.username LIKE ? OR u.phone_number LIKE ? OR d.transaction_id LIKE ?)";
        $tx_cond_w = "(u.username LIKE ? OR u.phone_number LIKE ? OR w.account_number LIKE ?)";
        $tx_types = "ssssss";
        $params = [$like_term, $like_term, $like_term, $like_term, $like_term, $like_term];
    }
    $query = "
        SELECT 'deposit' as type, d.amount, d.status, d.created_at, u.username 
        FROM deposits d JOIN users u ON d.user_id = u.id WHERE $tx_cond_d
        UNION ALL
        SELECT 'withdrawal' as type, w.amount, w.status, w.created_at, u.username 
        FROM withdrawals w JOIN users u ON w.user_id = u.id WHERE $tx_cond_w
        ORDER BY created_at DESC LIMIT 10
    ";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        if (!empty($search_term)) {
            $stmt->bind_param($tx_types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

/**
 * Fetches recent bets with an optional search term.
 * @param mysqli $conn The database connection.
 * @param string $search_term The term to search for.
 * @return array The list of recent bets.
 */
function get_recent_bets($conn, $search_term = '') {
    $bet_cond = "1=1";
    $bet_types = '';
    $params = [];
    if (!empty($search_term)) {
        $like_term = "%" . $search_term . "%";
        $bet_cond = "(u.username LIKE ? OR u.phone_number LIKE ? OR b.bet_number LIKE ?)";
        $bet_types = "sss";
        $params = [$like_term, $like_term, $like_term];
    }
    $query = "SELECT b.id, b.bet_number, b.amount, b.status, b.created_at, u.username 
              FROM bets b JOIN users u ON b.user_id = u.id 
              WHERE $bet_cond
              ORDER BY b.created_at DESC LIMIT 10";
    $stmt = $conn->prepare($query);
    if ($stmt) {
        if (!empty($search_term)) {
            $stmt->bind_param($bet_types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    return [];
}

// --- AJAX Handler for Live Search ---
if (isset($_GET['search_tx'])) {
    $search_tx = trim($_GET['search_tx']);
    $recent_transactions = get_recent_transactions($conn, $search_tx);
    
    if (count($recent_transactions) > 0):
        foreach ($recent_transactions as $tx): ?>
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 whitespace-nowrap">
                    <?php if ($tx['type'] == 'deposit'): ?>
                        <span class="text-green-600 font-bold"><i class="fas fa-arrow-down mr-1"></i> <?= __('admin_dash_type_deposit') ?></span>
                    <?php else: ?>
                        <span class="text-red-600 font-bold"><i class="fas fa-arrow-up mr-1"></i> <?= __('admin_dash_type_withdraw') ?></span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 font-bold whitespace-nowrap"><?= htmlspecialchars($tx['username']) ?></td>
                <td class="px-4 py-3 text-right font-bold whitespace-nowrap <?= $tx['type'] == 'deposit' ? 'text-green-600' : 'text-red-600' ?>">
                    <?= $tx['type'] == 'deposit' ? '+' : '-' ?><?= number_format($tx['amount']) ?> Ks
                </td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if ($tx['status'] == 'approved'): ?>
                        <span class="bg-green-100 text-green-700 text-[10px] px-2 py-1 rounded border border-green-300"><?= __('admin_dash_status_success') ?></span>
                    <?php elseif ($tx['status'] == 'pending'): ?>
                        <span class="bg-yellow-100 text-yellow-700 text-[10px] px-2 py-1 rounded border border-yellow-300"><?= __('admin_dash_status_pending') ?></span>
                    <?php else: ?>
                        <span class="bg-red-100 text-red-700 text-[10px] px-2 py-1 rounded border border-red-300"><?= __('admin_dash_status_rejected') ?></span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-right text-xs text-gray-500 whitespace-nowrap">
                    <?= date('d-M-Y h:i A', strtotime($tx['created_at'])) ?>
                </td>
            </tr>
        <?php endforeach;
    else: ?>
        <tr>
            <td colspan="5" class="px-4 py-8 text-center text-gray-500 italic"><?= __('admin_dash_no_records') ?></td>
        </tr>
    <?php endif;
    exit();
}

if (isset($_GET['search_bet'])) {
    $search_bet = trim($_GET['search_bet']);
    $recent_bets = get_recent_bets($conn, $search_bet);

    if (count($recent_bets) > 0):
        foreach ($recent_bets as $bet): ?>
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 font-bold whitespace-nowrap"><?= htmlspecialchars($bet['username']) ?></td>
                <td class="px-4 py-3 text-center font-bold text-blue-600 whitespace-nowrap tracking-wider"><?= htmlspecialchars($bet['bet_number']) ?></td>
                <td class="px-4 py-3 text-right font-bold text-red-600 whitespace-nowrap"><?= number_format($bet['amount']) ?> Ks</td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <?php if ($bet['status'] == 'win'): ?>
                        <span class="bg-green-100 text-green-700 text-[10px] px-2 py-1 rounded border border-green-300"><?= __('admin_dash_status_win') ?></span>
                    <?php elseif ($bet['status'] == 'pending'): ?>
                        <span class="bg-yellow-100 text-yellow-700 text-[10px] px-2 py-1 rounded border border-yellow-300"><?= __('admin_dash_status_pending') ?></span>
                    <?php else: ?>
                        <span class="bg-red-100 text-red-700 text-[10px] px-2 py-1 rounded border border-red-300"><?= __('admin_dash_status_lose') ?></span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-right text-xs text-gray-500 whitespace-nowrap">
                    <?= date('d-M-Y h:i A', strtotime($bet['created_at'])) ?>
                </td>
            </tr>
        <?php endforeach;
    else: ?>
        <tr>
            <td colspan="5" class="px-4 py-8 text-center text-gray-500 italic"><?= __('admin_dash_no_records') ?></td>
        </tr>
    <?php endif;
    exit();
}