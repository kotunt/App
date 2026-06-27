<?php
require_once __DIR__ . '/core/auth_check.php';
require_once __DIR__ . '/core/db_connect.php';
require_once __DIR__ . '/lang/language.php';

$me = (int) $_SESSION['user_id'];

$rows = [];
$sql = "SELECT u.id, u.username, u.avatar, u.vip_level,
               SUM(b.amount * b.odds) AS winnings, COUNT(*) AS wins
        FROM bets b
        JOIN users u ON u.id = b.user_id
        WHERE b.status = 'win' AND b.bet_section = '3d'
        GROUP BY u.id, u.username, u.avatar, u.vip_level
        ORDER BY winnings DESC
        LIMIT 100";
$res = $conn->query($sql);
if ($res) {
    $rows = $res->fetch_all(MYSQLI_ASSOC);
}

function mask_name($name) {
    $name = (string) $name;
    $len = mb_strlen($name, 'UTF-8');
    if ($len <= 2) return mb_substr($name, 0, 1, 'UTF-8') . '*';
    return mb_substr($name, 0, 2, 'UTF-8') . str_repeat('*', min(4, $len - 2));
}

$page_title = __('title_leaderboard') . " - Thai 2D3D";
require_once __DIR__ . '/includes/header.php';
?>
<body class="w-full md:max-w-4xl lg:max-w-5xl mx-auto min-h-screen bg-gray-50 md:shadow-2xl md:border-x border-gray-200 transition-all duration-300 pb-24 md:pb-28">

    <div class="bg-brand-gradient text-white flex items-center p-4 md:p-6 sticky top-0 z-20 shadow-card">
        <a href="index.php" class="mr-4 text-xl md:text-2xl w-6 md:w-10 hover:scale-110 transition-transform"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl md:text-2xl font-bold flex-1 text-center pr-6 md:pr-10 tracking-wide"><?= __('title_leaderboard') ?></h1>
    </div>

    <div class="px-4 md:px-8 pt-5 md:pt-7">
        <p class="text-gray-500 text-sm md:text-base font-medium mb-5 flex items-center">
            <i class="fas fa-crown text-gold-500 mr-2 text-lg"></i><?= __('leaderboard_subtitle') ?>
        </p>

        <?php if (count($rows) === 0): ?>
            <div class="bg-white rounded-3xl shadow-card border border-gray-100 p-10 text-center text-gray-400 mt-6">
                <i class="fas fa-trophy text-5xl mb-4 opacity-40"></i>
                <p class="font-bold text-sm md:text-base"><?= __('leaderboard_none') ?></p>
            </div>
        <?php else: ?>
            <?php
            $top = array_slice($rows, 0, 3);
            $rest = array_slice($rows, 3);
            $podium_order = [];
            if (isset($top[1])) $podium_order[] = [1, $top[1], 'order-1 mt-8'];
            if (isset($top[0])) $podium_order[] = [0, $top[0], 'order-2'];
            if (isset($top[2])) $podium_order[] = [2, $top[2], 'order-3 mt-12'];
            $medal = ['from-yellow-300 to-yellow-600', 'from-gray-300 to-gray-500', 'from-amber-500 to-amber-700'];
            $rank_icon = ['fa-crown', 'fa-medal', 'fa-medal'];
            ?>
            <div class="bg-brand-gradient rounded-3xl shadow-premium p-5 md:p-8 mb-6 relative overflow-hidden">
                <div class="absolute top-[-30%] right-[-10%] w-56 h-56 bg-gold-400/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="flex justify-center items-end gap-3 md:gap-6 relative z-10">
                    <?php foreach ($podium_order as $entry): list($idx, $row, $cls) = $entry; ?>
                        <div class="flex flex-col items-center <?= $cls ?> w-1/3 max-w-[120px]">
                            <div class="relative">
                                <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-gradient-to-b <?= $medal[$idx] ?> p-0.5 shadow-gold">
                                    <div class="w-full h-full rounded-full bg-white overflow-hidden flex items-center justify-center">
                                        <?php if (!empty($row['avatar'])): ?>
                                            <img src="<?= htmlspecialchars($row['avatar']) ?>" class="w-full h-full object-cover" alt="">
                                        <?php else: ?>
                                            <i class="fas fa-user text-2xl md:text-3xl text-primary"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <span class="absolute -top-2 -right-1 text-gold-400 text-xl md:text-2xl drop-shadow"><i class="fas <?= $rank_icon[$idx] ?>"></i></span>
                                <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 bg-gold-gradient text-primary-900 text-[10px] md:text-xs font-extrabold w-6 h-6 rounded-full flex items-center justify-center shadow"><?= $idx + 1 ?></span>
                            </div>
                            <p class="text-white font-bold text-xs md:text-sm mt-3 truncate w-full text-center"><?= htmlspecialchars(mask_name($row['username'])) ?></p>
                            <p class="text-gold-400 font-extrabold text-xs md:text-base"><?= number_format($row['winnings']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-card border border-gray-100 overflow-hidden divide-y divide-gray-100">
                <?php foreach ($rest as $i => $row): $rank = $i + 4; $isMe = ((int)$row['id'] === $me); ?>
                    <div class="flex items-center p-3.5 md:p-4 <?= $isMe ? 'bg-gold-gradient/10 border-l-4 border-gold-500' : '' ?>">
                        <div class="w-8 md:w-10 text-center font-extrabold text-gray-400 text-sm md:text-base"><?= $rank ?></div>
                        <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-gray-100 overflow-hidden flex items-center justify-center mx-2 md:mx-3 flex-shrink-0">
                            <?php if (!empty($row['avatar'])): ?>
                                <img src="<?= htmlspecialchars($row['avatar']) ?>" class="w-full h-full object-cover" alt="">
                            <?php else: ?>
                                <i class="fas fa-user text-gray-400"></i>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-800 text-sm md:text-base truncate">
                                <?= htmlspecialchars(mask_name($row['username'])) ?>
                                <?php if ($isMe): ?><span class="ml-1 text-[10px] bg-primary text-white px-2 py-0.5 rounded-full"><?= __('leaderboard_you') ?></span><?php endif; ?>
                            </p>
                            <p class="text-[11px] md:text-xs text-gray-400"><?= htmlspecialchars($row['vip_level']) ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-extrabold text-green-600 text-sm md:text-base">+<?= number_format($row['winnings']) ?></p>
                            <p class="text-[10px] md:text-xs text-gray-400"><?= __('currency') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
