<?php
require_once __DIR__ . '/core/auth_check.php';
require_once __DIR__ . '/core/db_connect.php';
require_once __DIR__ . '/lang/language.php';

$user_id = (int) $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$balance = $user ? floatval($user['balance']) : 0;

$games = [];
$g_res = $conn->query("SELECT name, provider, image_url, launch_url, badge FROM external_games WHERE is_active = 1 ORDER BY sort_order ASC, id DESC");
if ($g_res) $games = $g_res->fetch_all(MYSQLI_ASSOC);

$page_title = __('title_game_wallet') . " - Thai 2D3D";
require_once __DIR__ . '/includes/header.php';
?>
<body class="w-full md:max-w-4xl lg:max-w-5xl mx-auto min-h-screen bg-gray-50 md:shadow-2xl md:border-x border-gray-200 transition-all duration-300 pb-24 md:pb-28">

    <div class="bg-brand-gradient text-white flex items-center p-4 md:p-6 sticky top-0 z-20 shadow-card">
        <a href="index.php" class="mr-4 text-xl md:text-2xl w-6 md:w-10 hover:scale-110 transition-transform"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl md:text-2xl font-bold flex-1 text-center pr-6 md:pr-10 tracking-wide"><?= __('title_game_wallet') ?></h1>
    </div>

    <div class="px-4 md:px-8 pt-5 md:pt-7">
        <p class="text-gray-500 text-sm md:text-base font-medium mb-5 flex items-center">
            <i class="fas fa-gamepad text-primary mr-2 text-lg"></i><?= __('game_wallet_subtitle') ?>
        </p>

        <div class="grid grid-cols-2 gap-3 md:gap-4 mb-5">
            <div class="bg-brand-gradient text-white rounded-3xl shadow-card p-5 md:p-6 relative overflow-hidden">
                <div class="absolute top-[-30%] right-[-10%] w-32 h-32 bg-gold-400/10 rounded-full blur-2xl"></div>
                <p class="text-xs md:text-sm opacity-80 mb-1"><?= __('game_wallet_main') ?></p>
                <p class="text-xl md:text-3xl font-extrabold tracking-tight"><?= number_format($balance, 2) ?></p>
                <p class="text-[10px] md:text-xs opacity-70 mt-1"><?= __('currency') ?></p>
            </div>
            <div class="bg-white border border-gray-100 rounded-3xl shadow-card p-5 md:p-6">
                <p class="text-xs md:text-sm text-gray-400 mb-1"><?= __('game_wallet_game_balance') ?></p>
                <p class="text-xl md:text-3xl font-extrabold text-gray-800 tracking-tight">0.00</p>
                <p class="text-[10px] md:text-xs text-gray-400 mt-1"><?= __('currency') ?></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 md:gap-4 mb-3">
            <button onclick="gameComingSoon()" class="bg-white border border-gray-100 rounded-2xl shadow-sm py-3.5 font-bold text-primary text-sm md:text-base flex items-center justify-center gap-2 hover:-translate-y-0.5 transition-all">
                <i class="fas fa-arrow-right-to-bracket"></i> <?= __('game_wallet_transfer_in') ?>
            </button>
            <button onclick="gameComingSoon()" class="bg-white border border-gray-100 rounded-2xl shadow-sm py-3.5 font-bold text-primary text-sm md:text-base flex items-center justify-center gap-2 hover:-translate-y-0.5 transition-all">
                <i class="fas fa-arrow-right-from-bracket"></i> <?= __('game_wallet_transfer_out') ?>
            </button>
        </div>
        <p class="text-center text-[11px] md:text-xs text-gray-400 mb-6"><i class="fas fa-circle-info mr-1"></i><?= __('game_wallet_note') ?></p>

        <h2 class="font-bold text-gray-800 text-base md:text-lg mb-3 flex items-center"><i class="fas fa-dice text-gold-500 mr-2"></i><?= __('external_games_title') ?></h2>
        <?php if (count($games) === 0): ?>
            <div class="bg-white rounded-3xl shadow-card border border-gray-100 p-8 text-center text-gray-400">
                <i class="fas fa-ghost text-4xl mb-3 opacity-40"></i>
                <p class="font-bold text-sm"><?= __('game_coming_soon') ?></p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 gap-3 md:gap-4">
                <?php foreach ($games as $g): ?>
                    <div class="bg-white rounded-3xl shadow-card border border-gray-100 overflow-hidden card-hover">
                        <div class="h-28 md:h-36 bg-brand-gradient relative flex items-center justify-center">
                            <?php if (!empty($g['image_url'])): ?>
                                <img src="<?= htmlspecialchars($g['image_url']) ?>" class="w-full h-full object-cover absolute inset-0" alt="<?= htmlspecialchars($g['name']) ?>">
                            <?php else: ?>
                                <i class="fas fa-gamepad text-white/30 text-5xl"></i>
                            <?php endif; ?>
                            <?php if (!empty($g['badge'])): ?>
                                <span class="absolute top-2 left-2 bg-gold-gradient text-primary-900 text-[10px] font-extrabold px-2.5 py-1 rounded-full shadow-gold"><?= htmlspecialchars($g['badge']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="p-3 md:p-4">
                            <p class="font-bold text-gray-800 text-sm md:text-base truncate"><?= htmlspecialchars($g['name']) ?></p>
                            <p class="text-[11px] md:text-xs text-gray-400 mb-2"><?= htmlspecialchars($g['provider']) ?></p>
                            <?php if (!empty($g['launch_url'])): ?>
                                <a href="<?= htmlspecialchars(safe_url($g['launch_url'])) ?>" target="_blank" rel="noopener" class="block text-center bg-brand-gradient text-white font-bold text-xs md:text-sm py-2 rounded-xl"><?= __('play_now') ?></a>
                            <?php else: ?>
                                <button onclick="gameComingSoon()" class="block w-full text-center bg-gray-100 text-gray-400 font-bold text-xs md:text-sm py-2 rounded-xl"><?= __('game_coming_soon') ?></button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function gameComingSoon() {
            Swal.fire({ icon: 'info', title: '<?= __('game_coming_soon') ?>', text: '<?= __('game_wallet_note') ?>', confirmButtonColor: '#1a428a' });
        }
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
