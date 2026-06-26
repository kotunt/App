<?php
session_start();
require_once __DIR__ . '/../core/db_connect.php';
require_once __DIR__ . '/../core/auth_helper.php';

// Admin များသာ ဝင်ရောက်ခွင့်ရှိသည်
require_permission('can_manage_users');

// CSRF Token Check for AJAX
$csrf_token_header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($csrf_token_header) || !isset($_SESSION['admin_csrf_token']) || !hash_equals($_SESSION['admin_csrf_token'], $csrf_token_header)) {
    http_response_code(403);
    die(json_encode(['error' => 'Invalid security token.']));
}

// Date Filter ရယူခြင်း
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$search_term = trim($_GET['search_term'] ?? '');
$sort_by = $_GET['sort_by'] ?? 'id_desc';

$where_clause = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($start_date)) {
    $where_clause .= " AND DATE(created_at) >= ?";
    $params[] = $start_date;
    $types .= "s";
}
if (!empty($end_date)) {
    $where_clause .= " AND DATE(created_at) <= ?";
    $params[] = $end_date;
    $types .= "s";
}
if (!empty($search_term)) {
    $where_clause .= " AND (username LIKE ? OR phone_number LIKE ?)";
    $search_like = "%" . $search_term . "%";
    $params[] = $search_like;
    $params[] = $search_like;
    $types .= "ss";
}

$order_clause = "ORDER BY id DESC";
switch ($sort_by) {
    case 'id_asc': $order_clause = "ORDER BY id ASC"; break;
    case 'balance_desc': $order_clause = "ORDER BY balance DESC, id DESC"; break;
    case 'balance_asc': $order_clause = "ORDER BY balance ASC, id DESC"; break;
    case 'id_desc':
    default: $order_clause = "ORDER BY id DESC"; break;
}

// User အားလုံးကို Database မှ ဆွဲထုတ်ခြင်း
$users_query = "SELECT id, username, phone_number, balance, created_at, is_banned, verification_status, avatar, vip_level FROM users $where_clause $order_clause";

$stmt = $conn->prepare($users_query);
if (!empty($params)) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$all_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<h2 id="totalUserCount" class="font-bold text-gray-700"><?= __('admin_users_total_users') ?> <?= count($all_users) ?> <?= __('admin_users_unit_users') ?></h2>

<div id="userTableContainer" class="overflow-x-auto">
    <table class="min-w-full leading-normal text-left">
        <thead>
            <tr class="bg-blue-50 text-blue-800 font-bold border-b-2 border-blue-200 hidden md:table-row">
                <th class="px-5 py-4 text-sm whitespace-nowrap"><?= __('admin_users_col_id') ?></th>
                <th class="px-5 py-4 text-sm whitespace-nowrap"><?= __('admin_users_col_name') ?></th>
                <th class="px-5 py-4 text-sm whitespace-nowrap"><?= __('admin_users_col_edit_phone') ?></th>
                <th class="px-5 py-4 text-sm whitespace-nowrap"><?= __('admin_users_col_date') ?></th>
                <th class="px-5 py-4 text-sm whitespace-nowrap"><?= __('admin_users_col_avatar') ?></th>
                <th class="px-5 py-4 text-sm whitespace-nowrap"><?= __('admin_users_col_edit_bal') ?></th>
                <th class="px-5 py-4 text-sm whitespace-nowrap"><?= __('admin_users_col_edit_pwd') ?></th>
                <th class="px-5 py-4 text-sm whitespace-nowrap text-center"><?= __('admin_users_col_action') ?></th>
            </tr>
        </thead>
        <tbody id="userTableBody">
            <?php if (count($all_users) > 0): ?>
                <?php foreach ($all_users as $u): ?>
                <tr class="border-b border-gray-200 hover:bg-gray-50 transition duration-150 hidden md:table-row">
                    <td class="px-5 py-4 text-sm text-gray-600 font-bold">#<?= $u['id'] ?></td>
                    <td class="px-5 py-4 text-sm text-gray-800 font-bold">
                        <a href="admin_user_history.php?user_id=<?= $u['id'] ?>" class="hover:underline"><?= htmlspecialchars($u['username']) ?></a>
                        <?php if($u['id'] == 1) echo '<span class="ml-2 text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded border border-red-200">' . __('admin_role_main') . '</span>'; ?>
                        <?php if(isset($u['is_banned']) && $u['is_banned']) echo '<span class="ml-2 text-[10px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded border border-gray-300"><i class="fas fa-ban"></i> ' . __('admin_users_ban') . '</span>'; ?>
                        <?php if(isset($u['vip_level']) && $u['vip_level'] !== 'Standard'): ?>
                            <span class="ml-2 text-[10px] bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded border border-yellow-300" title="VIP Level"><i class="fas fa-crown"></i> <?= htmlspecialchars($u['vip_level']) ?></span>
                        <?php endif; ?>
                        <?php if(isset($u['verification_status']) && $u['verification_status'] == 'pending') echo '<span class="ml-2 text-[10px] bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded border border-yellow-300">' . __('status_pending') . '</span>'; ?>
                        <?php if(isset($u['verification_status']) && $u['verification_status'] == 'rejected') echo '<span class="ml-2 text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded border border-red-300">' . __('admin_users_reject') . '</span>'; ?>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <form method="POST" action="" class="flex items-center space-x-2">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                            <input type="text" name="new_phone" value="<?= htmlspecialchars($u['phone_number']) ?>" class="w-32 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:border-blue-500 font-mono text-gray-700" required>
                            <button type="submit" name="update_phone" class="bg-teal-500 hover:bg-teal-600 text-white px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition" title="<?= __('admin_users_btn_edit_phone') ?>" onclick="return confirm('<?= sprintf(__('admin_users_confirm_edit_phone'), htmlspecialchars($u['username'])) ?>');">
                                <i class="fas fa-save"></i>
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-4 text-xs text-gray-500"><?= date('d-M-Y h:i A', strtotime($u['created_at'])) ?></td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <div class="flex items-center space-x-2">
                            <?php if (!empty($u['avatar'])): ?>
                            <img src="../<?= ltrim(htmlspecialchars($u['avatar']), '../') ?>" class="w-8 h-8 rounded-full object-cover border border-gray-300">
                                <form method="POST" action="" onsubmit="return confirm('<?= __('admin_users_confirm_delete_avatar') ?>');" class="inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                                    <input type="hidden" name="action" value="remove_avatar">
                                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="<?= __('admin_users_btn_delete_avatar') ?>"><i class="fas fa-trash"></i></button>
                                </form>
                            <?php else: ?>
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-gray-500"><i class="fas fa-user text-xs"></i></div>
                            <?php endif; ?>
                            <div class="inline ml-1">
                                <label class="cursor-pointer bg-gray-100 hover:bg-gray-200 border border-gray-300 px-2 py-1 rounded text-xs text-gray-600 transition" title="<?= __('admin_users_btn_upload_avatar') ?>">
                                    <i class="fas fa-upload"></i>
                                    <input type="file" class="hidden" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="openCropper(event, <?= $u['id'] ?>)">
                                </label>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <form method="POST" action="" class="flex items-center space-x-2">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                            <input type="number" name="new_balance" value="<?= (float)$u['balance'] ?>" step="0.01" min="0" class="w-28 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:border-blue-500 font-bold text-primary" required>
                            <button type="submit" name="update_balance" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition" onclick="return confirm('<?= sprintf(__('admin_users_confirm_edit_bal'), htmlspecialchars($u['username'])) ?>');">
                                <i class="fas fa-save mr-1"></i> <?= __('admin_users_btn_save') ?>
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">
                        <form method="POST" action="" class="flex items-center space-x-2">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                            <input type="text" name="new_password" placeholder="<?= __('admin_users_ph_new_pwd') ?>" minlength="6" class="w-24 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:border-blue-500" required>
                            <button type="submit" name="update_password" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition" title="<?= __('admin_users_btn_change_pwd') ?>" onclick="return confirm('<?= sprintf(__('admin_users_confirm_change_pwd'), htmlspecialchars($u['username'])) ?>');">
                                <i class="fas fa-key"></i>
                            </button>
                        </form>
                    </td>
                <td class="px-5 py-3 whitespace-nowrap text-center">
                    <a href="admin_user_history.php?user_id=<?= $u['id'] ?>" class="bg-indigo-100 text-indigo-700 hover:bg-indigo-200 px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition inline-block"><i class="fas fa-list mr-1"></i> <?= __('admin_users_action_history') ?></a>
                    <a href="admin_user_commissions.php?user_id=<?= $u['id'] ?>" class="bg-emerald-100 text-emerald-700 hover:bg-emerald-200 px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition inline-block mb-1"><i class="fas fa-hand-holding-usd mr-1"></i> <?= __('admin_users_action_comm') ?></a>
                    <a href="admin_user_referrals.php?user_id=<?= $u['id'] ?>" class="bg-orange-100 text-orange-700 hover:bg-orange-200 px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition inline-block mb-1"><i class="fas fa-users mr-1"></i> <?= __('admin_users_action_ref') ?></a>
                    <?php if($u['id'] != 1): ?>
                        <?php if(check_permission('can_manage_transactions')): ?>
                            <a href="admin_deposit.php?user_id=<?= $u['id'] ?>" class="bg-pink-100 text-pink-700 hover:bg-pink-200 px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition inline-block mb-1"><i class="fas fa-donate mr-1"></i> <?= __('admin_users_action_transfer') ?></a>
                        <?php endif; ?>
                        <a href="admin_notifications.php?user_id=<?= $u['id'] ?>" class="bg-yellow-100 text-yellow-700 hover:bg-yellow-200 px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition inline-block"><i class="fas fa-envelope mr-1"></i> <?= __('admin_users_action_noti') ?></a>
                        <a href="admin_send_message.php?user_id=<?= $u['id'] ?>" class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition inline-block mb-1"><i class="fas fa-comment-dots mr-1"></i> <?= __('admin_users_action_msg') ?></a>
                        <form method="POST" action="" class="inline-block mb-1" onsubmit="return confirm('<?= sprintf(__('admin_users_confirm_login_as'), htmlspecialchars($u['username'])) ?>');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                            <button type="submit" name="login_as_user" class="bg-purple-100 text-purple-700 hover:bg-purple-200 px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition" title="<?= __('admin_users_btn_login_as') ?>"><i class="fas fa-sign-in-alt mr-1"></i> <?= __('admin_users_btn_login_as') ?></button>
                        </form>
                        <form method="POST" action="" class="inline-block">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                            <input type="hidden" name="current_status" value="<?= $u['is_banned'] ? 1 : 0 ?>">
                            <?php if($u['is_banned']): ?>
                                <button type="submit" name="toggle_ban" class="bg-green-100 text-green-700 hover:bg-green-200 px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition" onclick="return confirm('<?= __('admin_users_confirm_unban') ?>');"><i class="fas fa-unlock mr-1"></i> <?= __('admin_users_unban') ?></button>
                            <?php else: ?>
                                <button type="submit" name="toggle_ban" class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition" onclick="return confirm('<?= __('admin_users_confirm_ban') ?>');"><i class="fas fa-ban mr-1"></i> <?= __('admin_users_ban') ?></button>
                            <?php endif; ?>
                        </form>
                        <form method="POST" action="" class="inline-block mb-1 ml-1" onsubmit="return confirm('<?= __('admin_users_confirm_delete') ?>');">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                            <button type="submit" name="delete_user" class="bg-gray-800 text-white hover:bg-gray-900 px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition"><i class="fas fa-trash-alt mr-1"></i> <?= __('delete') ?></button>
                        </form>
                    <?php endif; ?>
                    <?php if(isset($u['verification_status']) && $u['verification_status'] == 'pending'): ?>
                        <form method="POST" action="" class="mt-2 block w-full text-left">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                            <button type="submit" name="verify_user" value="1" onclick="document.getElementById('verify_action_<?= $u['id'] ?>').value='approved'; return confirm('<?= __('admin_users_confirm_approve') ?>');" class="bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1.5 rounded text-[11px] font-bold shadow-sm transition"><i class="fas fa-check mr-1"></i> <?= __('admin_users_approve') ?></button>
                            <button type="submit" name="verify_user" value="1" onclick="document.getElementById('verify_action_<?= $u['id'] ?>').value='rejected'; return confirm('<?= __('admin_users_confirm_reject') ?>');" class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1.5 rounded text-[11px] font-bold shadow-sm transition ml-1"><i class="fas fa-times mr-1"></i> <?= __('admin_users_reject') ?></button>
                            <input type="hidden" name="verify_action" id="verify_action_<?= $u['id'] ?>" value="">
                        </form>
                    <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>

                <!-- Mobile Card View -->
                <?php foreach ($all_users as $u): ?>
                <div class="md:hidden p-4 border-b border-gray-200 bg-white">
                    <div class="flex items-start gap-4">
                        <div class="relative shrink-0">
                            <?php if (!empty($u['avatar'])): ?>
                                <img src="../<?= ltrim(htmlspecialchars($u['avatar']), '../') ?>" class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-md">
                            <?php else: ?>
                                <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500"><i class="fas fa-user text-lg"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-gray-800"><?= htmlspecialchars($u['username']) ?></p>
                            <p class="text-xs text-gray-500 font-mono"><?= htmlspecialchars($u['phone_number']) ?></p>
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                <?php if($u['id'] == 1) echo '<span class="text-[9px] bg-red-100 text-red-600 px-1.5 py-0.5 rounded border border-red-200">Admin</span>'; ?>
                                <?php if(isset($u['is_banned']) && $u['is_banned']) echo '<span class="text-[9px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded border border-gray-300"><i class="fas fa-ban"></i> ' . __('admin_users_ban') . '</span>'; ?>
                                <?php if(isset($u['vip_level']) && $u['vip_level'] !== 'Standard') echo '<span class="text-[9px] bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded border border-yellow-300" title="VIP Level"><i class="fas fa-crown"></i> '.htmlspecialchars($u['vip_level']).'</span>'; ?>
                                <?php if(isset($u['verification_status']) && $u['verification_status'] == 'pending') echo '<span class="text-[9px] bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded border border-yellow-300">' . __('status_pending') . '</span>'; ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-blue-600"><?= number_format($u['balance']) ?> Ks</p>
                            <p class="text-[10px] text-gray-400"><?= date('d-M-y', strtotime($u['created_at'])) ?></p>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap gap-2 justify-end">
                        <a href="admin_user_history.php?user_id=<?= $u['id'] ?>" class="bg-indigo-100 text-indigo-700 hover:bg-indigo-200 px-3 py-1.5 rounded-lg text-[10px] font-bold shadow-sm transition inline-block"><i class="fas fa-list mr-1"></i> <?= __('admin_users_action_history') ?></a>
                        <a href="admin_user_commissions.php?user_id=<?= $u['id'] ?>" class="bg-emerald-100 text-emerald-700 hover:bg-emerald-200 px-3 py-1.5 rounded-lg text-[10px] font-bold shadow-sm transition inline-block"><i class="fas fa-hand-holding-usd mr-1"></i> <?= __('admin_users_action_comm') ?></a>
                        <?php if($u['id'] != 1): ?>
                            <?php if(check_permission('can_manage_transactions')): ?>
                                <a href="admin_deposit.php?user_id=<?= $u['id'] ?>" class="bg-pink-100 text-pink-700 hover:bg-pink-200 px-3 py-1.5 rounded-lg text-[10px] font-bold shadow-sm transition inline-block"><i class="fas fa-donate mr-1"></i> <?= __('admin_users_action_transfer') ?></a>
                            <?php endif; ?>
                            <form method="POST" action="" class="inline-block" onsubmit="return confirm('<?= sprintf(__('admin_users_confirm_login_as'), htmlspecialchars($u['username'])) ?>');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                                <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                <button type="submit" name="login_as_user" class="bg-purple-100 text-purple-700 hover:bg-purple-200 px-3 py-1.5 rounded-lg text-[10px] font-bold shadow-sm transition" title="<?= __('admin_users_btn_login_as') ?>"><i class="fas fa-sign-in-alt mr-1"></i> <?= __('admin_users_btn_login_as') ?></button>
                            </form>
                            <form method="POST" action="" class="inline-block">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                                <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="current_status" value="<?= $u['is_banned'] ? 1 : 0 ?>">
                                <?php if($u['is_banned']): ?>
                                    <button type="submit" name="toggle_ban" class="bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1.5 rounded-lg text-[10px] font-bold shadow-sm transition" onclick="return confirm('<?= __('admin_users_confirm_unban') ?>');"><i class="fas fa-unlock mr-1"></i> <?= __('admin_users_unban') ?></button>
                                <?php else: ?>
                                    <button type="submit" name="toggle_ban" class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1.5 rounded-lg text-[10px] font-bold shadow-sm transition" onclick="return confirm('<?= __('admin_users_confirm_ban') ?>');"><i class="fas fa-ban mr-1"></i> <?= __('admin_users_ban') ?></button>
                                <?php endif; ?>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

            <?php else: ?>
                <tr class="md:table-row">
                    <td colspan="8" class="px-5 py-8 text-center text-gray-500 italic"><?= __('admin_users_no_results') ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>