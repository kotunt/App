<?php
session_start();
require_once __DIR__ . '/../core/db_connect.php';
require_once __DIR__ . '/../core/image_helper.php';
require_once __DIR__ . '/../core/auth_helper.php';
require_permission('can_manage_users');

// Include the new controller to handle all POST logic
require_once __DIR__ . '/controllers/UsersController.php';

// CSRF Token
if (empty($_SESSION['admin_csrf_token'])) {
    $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
}

// Handle all POST requests and get any success/error messages
$messages = handle_post_requests($conn);
$success_message = $messages['success_message'];
$error_message = $messages['error_message'];

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

<?php 
$page_title = __('admin_manage_users') . " - Admin";
require_once __DIR__ . '/../includes/header.php'; 
?>

<!-- Cropper.js ထည့်သွင်းခြင်း -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<body class="max-w-5xl mx-auto min-h-screen bg-gray-100 shadow-xl pb-10">

    <?php
    $header_title = __('admin_manage_users');
    $header_icon = "fas fa-users-cog";
    require_once __DIR__ . '/admin_header.php';
    ?>

    <div class="p-4 md:p-6 pt-0">
        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6 text-sm shadow-sm"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6 text-sm shadow-sm"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="mb-4 text-right flex flex-wrap justify-end gap-2">
            <button type="button" onclick="document.getElementById('bulkImportForm').classList.toggle('hidden'); document.getElementById('addUserForm').classList.add('hidden');" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition"><i class="fas fa-file-import mr-2"></i> <?= __('admin_users_btn_csv_import') ?></button>
            <button type="button" onclick="document.getElementById('addUserForm').classList.toggle('hidden'); document.getElementById('bulkImportForm').classList.add('hidden');" class="bg-primary hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition"><i class="fas fa-user-plus mr-2"></i> <?= __('admin_users_btn_add_user') ?></button>
        </div>
        
        <!-- Add New User Form -->
        <form id="addUserForm" method="POST" action="" class="hidden bg-white p-6 rounded-xl shadow-md border-t-4 border-primary mb-6 transition-all duration-300">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
            <h2 class="font-bold text-gray-800 mb-4 border-b pb-2"><i class="fas fa-user-plus text-primary mr-2"></i> <?= __('admin_users_title_add_user') ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2"><?= __('admin_users_label_name') ?></label>
                    <input type="text" name="new_username" placeholder="<?= __('admin_users_ph_name') ?>" class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:border-blue-500" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2"><?= __('admin_users_label_phone') ?></label>
                    <input type="text" name="new_phone" placeholder="09xxxxxxxxx" class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:border-blue-500" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2"><?= __('admin_users_label_password') ?></label>
                    <input type="text" name="new_user_password" placeholder="<?= __('admin_users_ph_password') ?>" minlength="6" class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:border-blue-500" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2"><?= __('admin_users_label_initial_balance') ?></label>
                    <input type="number" name="initial_balance" value="0" min="0" step="0.01" class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:border-blue-500" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2"><?= __('admin_users_label_vip') ?></label>
                    <select name="new_vip_level" class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:border-blue-500">
                        <option value="Standard">Standard</option>
                        <option value="Bronze">Bronze</option>
                        <option value="Silver">Silver</option>
                        <option value="Gold">Gold</option>
                        <option value="Diamond">Diamond</option>
                    </select>
                </div>
            </div>
            <div class="text-right">
                <button type="submit" name="add_user" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm font-bold shadow-sm transition"><i class="fas fa-save mr-1"></i> <?= __('admin_users_btn_create_account') ?></button>
            </div>
        </form>

        <!-- Bulk Import Form -->
        <form id="bulkImportForm" method="POST" action="" enctype="multipart/form-data" class="hidden bg-white p-6 rounded-xl shadow-md border-t-4 border-purple-500 mb-6 transition-all duration-300">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
            <div class="flex flex-wrap justify-between items-center border-b pb-2 mb-4 gap-2">
                <h2 class="font-bold text-gray-800"><i class="fas fa-file-import text-purple-500 mr-2"></i> <?= __('admin_users_title_bulk_import') ?></h2>
                <a href="data:text/csv;charset=utf-8,Username,Phone,Password,Balance%0AUser1,09111111111,123456,1000%0AUser2,09222222222,123456,0" download="sample_users.csv" class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 py-1.5 px-3 rounded shadow-sm transition font-bold"><i class="fas fa-download mr-1"></i> <?= __('admin_users_btn_download_csv') ?></a>
            </div>
            
            <div class="flex flex-wrap items-center gap-4 mb-2">
                <input type="file" name="csv_file" accept=".csv" required class="flex-1 min-w-[200px] py-2 px-3 border border-gray-300 rounded text-sm focus:outline-none focus:border-purple-500 bg-gray-50">
                <button type="submit" name="bulk_import_users" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg text-sm font-bold shadow-sm transition whitespace-nowrap"><i class="fas fa-upload mr-1"></i> <?= __('admin_users_btn_upload_import') ?></button>
            </div>
            <div class="mt-4 text-xs text-gray-600 bg-purple-50 p-4 rounded border border-purple-100">
                <p class="font-bold text-purple-800 mb-2"><i class="fas fa-info-circle mr-1"></i> <?= __('admin_users_note_title') ?></p>
                <ul class="list-disc list-inside space-y-1 ml-1">
                    <li><?= __('admin_users_note_1') ?></li>
                    <li><?= __('admin_users_note_2') ?></li>
                    <li><?= __('admin_users_note_3') ?></li>
                </ul>
            </div>
        </form>

        <!-- Hidden Form for Cropped Avatar Submission -->
        <form id="cropForm" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
            <input type="hidden" name="action" value="update_avatar">
            <input type="hidden" name="target_user_id" id="crop_target_user_id" value="">
            <input type="hidden" name="cropped_avatar_data" id="cropped_avatar_data" value="">
        </form>

        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-4 border-b bg-gray-50 flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
                <div class="flex flex-col gap-3">
                    <h2 id="totalUserCount" class="font-bold text-gray-700"><?= __('admin_users_total_users') ?> <?= count($all_users) ?> <?= __('admin_users_unit_users') ?></h2>
                    <!-- Date Filter Form -->
                    <form method="GET" action="" class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-2">
                        <input type="text" name="search_term" value="<?= htmlspecialchars($search_term) ?>" oninput="liveSearch()" placeholder="<?= __('admin_users_ph_search') ?>" class="px-3 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-500">
                        <select name="sort_by" onchange="liveSearch()" class="px-3 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-500">
                            <option value="id_desc" <?= $sort_by == 'id_desc' ? 'selected' : '' ?>><?= __('admin_users_sort_latest') ?></option>
                            <option value="id_asc" <?= $sort_by == 'id_asc' ? 'selected' : '' ?>><?= __('admin_users_sort_oldest') ?></option>
                            <option value="balance_desc" <?= $sort_by == 'balance_desc' ? 'selected' : '' ?>><?= __('admin_users_sort_max_bal') ?></option>
                            <option value="balance_asc" <?= $sort_by == 'balance_asc' ? 'selected' : '' ?>><?= __('admin_users_sort_min_bal') ?></option>
                        </select>
                        <div class="flex items-center gap-2">
                            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" onchange="liveSearch()" class="px-3 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-500">
                            <span class="text-gray-500 text-sm hidden sm:inline"><?= __('admin_users_to') ?></span>
                            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" onchange="liveSearch()" class="px-3 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:border-blue-500">
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-sm font-bold shadow-sm transition"><i class="fas fa-search"></i></button>
                        <?php if(!empty($start_date) || !empty($end_date) || !empty($search_term) || $sort_by != 'id_desc'): ?>
                            <a href="admin_users.php" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-1.5 rounded text-sm font-bold shadow-sm transition" title="<?= __('admin_users_btn_clear') ?>"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="flex gap-2 self-start md:self-center">
                    <form action="admin_export.php" method="GET" class="flex items-center gap-2">
                        <select name="period" class="px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:border-green-500">
                            <option value="all"><?= __('admin_users_period_all') ?></option>
                            <option value="today"><?= __('admin_users_period_today') ?></option>
                            <option value="this_week"><?= __('admin_users_period_week') ?></option>
                            <option value="this_month"><?= __('admin_users_period_month') ?></option>
                        </select>
                        <button type="submit" name="type" value="commissions" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm font-bold shadow-sm transition"><i class="fas fa-file-excel mr-1"></i> <?= __('admin_users_export_comm') ?></button>
                        <button type="submit" name="type" value="users" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm font-bold shadow-sm transition"><i class="fas fa-file-excel mr-1"></i> <?= __('admin_users_export_users') ?></button>
                    </form>
                </div>
            </div>
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
                                <div class="relative inline-block text-left" data-dropdown-container>
                                    <div>
                                        <button onclick="toggleDropdown(this)" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-indigo-500" aria-haspopup="true" aria-expanded="false">
                                            <?= __('Actions') ?>
                                            <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 z-10 hidden" role="menu" aria-orientation="vertical">
                                        <div class="py-1" role="none">
                                            <a href="admin_user_history.php?user_id=<?= $u['id'] ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem"><i class="fas fa-list w-5 mr-2 text-indigo-500"></i> <?= __('admin_users_action_history') ?></a>
                                            <a href="admin_user_commissions.php?user_id=<?= $u['id'] ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem"><i class="fas fa-hand-holding-usd w-5 mr-2 text-emerald-500"></i> <?= __('admin_users_action_comm') ?></a>
                                            <a href="admin_user_referrals.php?user_id=<?= $u['id'] ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem"><i class="fas fa-users w-5 mr-2 text-orange-500"></i> <?= __('admin_users_action_ref') ?></a>
                                        </div>
                                        <?php if($u['id'] != 1): ?>
                                            <div class="py-1" role="none">
                                                <?php if(check_permission('can_manage_transactions')): ?>
                                                    <a href="admin_deposit.php?user_id=<?= $u['id'] ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem"><i class="fas fa-donate w-5 mr-2 text-pink-500"></i> <?= __('admin_users_action_transfer') ?></a>
                                                <?php endif; ?>
                                                <a href="admin_notifications.php?user_id=<?= $u['id'] ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem"><i class="fas fa-envelope w-5 mr-2 text-yellow-500"></i> <?= __('admin_users_action_noti') ?></a>
                                                <a href="admin_send_message.php?user_id=<?= $u['id'] ?>" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem"><i class="fas fa-comment-dots w-5 mr-2 text-blue-500"></i> <?= __('admin_users_action_msg') ?></a>
                                            </div>
                                            <div class="py-1" role="none">
                                                <form method="POST" action="" class="block" onsubmit="return confirm('<?= sprintf(__('admin_users_confirm_login_as'), htmlspecialchars($u['username'])) ?>');" role="menuitem">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                                                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                    <button type="submit" name="login_as_user" class="w-full text-left flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" title="<?= __('admin_users_btn_login_as') ?>"><i class="fas fa-sign-in-alt w-5 mr-2 text-purple-500"></i> <?= __('admin_users_btn_login_as') ?></button>
                                                </form>
                                                <form method="POST" action="" class="block" role="menuitem">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                                                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                    <input type="hidden" name="current_status" value="<?= $u['is_banned'] ? 1 : 0 ?>">
                                                    <?php if($u['is_banned']): ?>
                                                        <button type="submit" name="toggle_ban" class="w-full text-left flex items-center px-4 py-2 text-sm text-green-600 hover:bg-gray-100 hover:text-green-800" onclick="return confirm('<?= __('admin_users_confirm_unban') ?>');"><i class="fas fa-unlock w-5 mr-2"></i> <?= __('admin_users_unban') ?></button>
                                                    <?php else: ?>
                                                        <button type="submit" name="toggle_ban" class="w-full text-left flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100 hover:text-red-800" onclick="return confirm('<?= __('admin_users_confirm_ban') ?>');"><i class="fas fa-ban w-5 mr-2"></i> <?= __('admin_users_ban') ?></button>
                                                    <?php endif; ?>
                                                </form>
                                            </div>
                                            <div class="py-1" role="none">
                                                <form method="POST" action="" class="block" onsubmit="return confirm('<?= __('admin_users_confirm_delete') ?>');" role="menuitem">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                                                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                    <button type="submit" name="delete_user" class="w-full text-left flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100 hover:text-red-800"><i class="fas fa-trash-alt w-5 mr-2"></i> <?= __('delete') ?></button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                        <?php if(isset($u['verification_status']) && $u['verification_status'] == 'pending'): ?>
                                            <div class="py-1" role="none">
                                                <form method="POST" action="" class="block" role="menuitem">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf_token']) ?>">
                                                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                    <input type="hidden" name="verify_action" id="verify_action_<?= $u['id'] ?>" value="">
                                                    <button type="submit" name="verify_user" value="1" onclick="document.getElementById('verify_action_<?= $u['id'] ?>').value='approved'; return confirm('<?= __('admin_users_confirm_approve') ?>');" class="w-full text-left flex items-center px-4 py-2 text-sm text-green-600 hover:bg-gray-100 hover:text-green-800"><i class="fas fa-check w-5 mr-2"></i> <?= __('admin_users_approve') ?></button>
                                                    <button type="submit" name="verify_user" value="1" onclick="document.getElementById('verify_action_<?= $u['id'] ?>').value='rejected'; return confirm('<?= __('admin_users_confirm_reject') ?>');" class="w-full text-left flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100 hover:text-red-800 mt-1"><i class="fas fa-times w-5 mr-2"></i> <?= __('admin_users_reject') ?></button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
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
        </div>
    </div>

    <!-- Cropper Modal -->
    <div id="cropperModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white p-4 rounded-xl shadow-lg w-full max-w-md">
            <h3 class="text-lg font-bold text-gray-800 mb-3"><i class="fas fa-crop-alt mr-2 text-primary"></i> <?= __('admin_users_crop_title') ?></h3>
            <div class="w-full h-64 bg-gray-100 mb-4 rounded overflow-hidden">
                <img id="imageToCrop" src="" class="max-w-full max-h-full block mx-auto">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="cancelCrop()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg text-gray-800 font-bold transition"><?= __('admin_users_btn_cancel') ?></button>
                <button type="button" onclick="applyCrop()" class="px-4 py-2 bg-primary hover:bg-blue-800 text-white rounded-lg font-bold transition"><i class="fas fa-check mr-1"></i> <?= __('admin_users_btn_crop') ?></button>
            </div>
        </div>
    </div>

    <script>
        let searchTimeout;

        function toggleDropdown(button) {
            const dropdown = button.closest('[data-dropdown-container]').querySelector('[role="menu"]');
            const isHidden = dropdown.classList.contains('hidden');

            // Close all other open dropdowns first to avoid multiple open menus
            document.querySelectorAll('[data-dropdown-container] [role="menu"]').forEach(d => {
                if (d !== dropdown) {
                    d.classList.add('hidden');
                }
            });

            // Toggle the clicked dropdown
            dropdown.classList.toggle('hidden');

            // Add a one-time event listener to close the dropdown when clicking outside
            if (!isHidden) {
                // If the dropdown was just closed by the toggle, don't add the listener
                return;
            }
            
            const closeHandler = (event) => {
                const container = button.closest('[data-dropdown-container]');
                if (!container.contains(event.target)) {
                    dropdown.classList.add('hidden');
                    document.removeEventListener('click', closeHandler);
                }
            };
            // Use a timeout to prevent the event from firing immediately on the same click
            setTimeout(() => document.addEventListener('click', closeHandler), 0);
        }
        
        function liveSearch() {
            clearTimeout(searchTimeout);
            
            // 300ms စောင့်ပြီးမှ Database သို့ Request ပို့မည် (စာလုံးရိုက်တိုင်း Server ကို မပို့စေရန်)
            searchTimeout = setTimeout(() => {
                let searchTerm = document.querySelector('input[name="search_term"]').value;
                let startDate = document.querySelector('input[name="start_date"]').value || '';
                let endDate = document.querySelector('input[name="end_date"]').value || '';
                let sortBy = document.querySelector('select[name="sort_by"]').value || 'id_desc';
                
                let url = `ajax_user_search.php?search_term=${encodeURIComponent(searchTerm)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&sort_by=${encodeURIComponent(sortBy)}`;
                
                let container = document.getElementById('userTableContainer');
                if (container) container.style.opacity = '0.5'; // Loading ဖြစ်နေကြောင်းပြသရန် အနည်းငယ်မှိန်လိုက်မည်
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(url, {
                    headers: {
                        'X-CSRF-Token': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.text())
                    .then(html => {
                        let parser = new DOMParser();
                        let doc = parser.parseFromString(html, 'text/html');
                        
                        let newCount = doc.getElementById('totalUserCount');
                        
                        if (newTbody) tbody.innerHTML = newTbody.innerHTML;
                        if (newCount) document.getElementById('totalUserCount').innerHTML = newCount.innerHTML;
                        
                        tbody.style.opacity = '1';
                    })
                    .catch(err => {
                        console.error('Search error:', err);
                        tbody.style.opacity = '1';
                    });
            }, 300);
        }
        
        // Cropper JS 
        let cropper = null;
        let currentTargetUserId = null;
        
        function openCropper(event, userId) {
            const files = event.target.files;
            if (files && files.length > 0) {
                currentTargetUserId = userId;
                const file = files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imageToCrop = document.getElementById('imageToCrop');
                    imageToCrop.src = e.target.result;
                    document.getElementById('cropperModal').classList.remove('hidden');
                    if (cropper) cropper.destroy();
                    cropper = new Cropper(imageToCrop, {
                        aspectRatio: 1, // လေးထောင့် (Square)
                        viewMode: 1,
                        autoCropArea: 1,
                    });
                };
                reader.readAsDataURL(file);
            }
            // တစ်ပုံတည်းကို နောက်တစ်ခါ ပြန်ရွေးလို့ရအောင် reset ချပေးမည်
            event.target.value = '';
        }
        
        function cancelCrop() {
            document.getElementById('cropperModal').classList.add('hidden');
            if (cropper) cropper.destroy();
            currentTargetUserId = null;
        }

        function applyCrop() {
            if (!cropper || !currentTargetUserId) return;
            const canvas = cropper.getCroppedCanvas({ width: 400, height: 400 });
            document.getElementById('crop_target_user_id').value = currentTargetUserId;
            document.getElementById('cropped_avatar_data').value = canvas.toDataURL('image/webp', 0.8);
            document.getElementById('cropperModal').classList.add('hidden');
            cropper.destroy(); cropper = null;
            document.getElementById('cropForm').submit();
        }
    </script>
</body>
</html>