<?php
require_once __DIR__ . '/core/auth_check.php';
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/lang/language.php';

$set = [];
$set_res = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('max_limit_per_number','max_limit_per_3d_number','dynamic_odds_threshold')");
if ($set_res) {
    while ($r = $set_res->fetch_assoc()) $set[$r['setting_key']] = $r['setting_value'];
}
$limit_2d = floatval($set['max_limit_per_number'] ?? 20000);
$limit_3d = floatval($set['max_limit_per_3d_number'] ?? 10000);
$threshold = floatval($set['dynamic_odds_threshold'] ?? 80);
if ($limit_2d <= 0) $limit_2d = 20000;
if ($limit_3d <= 0) $limit_3d = 10000;

function fetch_hot($conn, $sections_sql, $len, $limit) {
    $rows = [];
    $sql = "SELECT bet_number, SUM(amount) AS total
            FROM bets
            WHERE status = 'pending' AND bet_section IN ($sections_sql) AND CHAR_LENGTH(bet_number) = $len
            GROUP BY bet_number
            HAVING total > 0
            ORDER BY total DESC
            LIMIT 60";
    $res = $conn->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $pct = min(100, round(($r['total'] / $limit) * 100));
            $rows[] = ['number' => $r['bet_number'], 'total' => floatval($r['total']), 'pct' => $pct];
        }
    }
    return $rows;
}
$hot_2d = fetch_hot($conn, "'morning','evening'", 2, $limit_2d);
$hot_3d = fetch_hot($conn, "'3d'", 3, $limit_3d);

$page_title = __('title_hot_numbers') . " - Thai 2D3D";
require_once __DIR__ . '/includes/header.php';

$render_hot = function ($rows, $threshold) {
    if (count($rows) === 0) {
        echo '<div class="bg-white rounded-3xl shadow-card border border-gray-100 p-10 text-center text-gray-400"><i class="fas fa-fire-flame-simple text-5xl mb-3 opacity-40"></i><p class="font-bold text-sm md:text-base">' . __('hot_none') . '</p></div>';
        return;
    }
    echo '<div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">';
    foreach ($rows as $r) {
        $pct = $r['pct'];
        $full = $pct >= 100;
        $hot = $pct >= $threshold;
        $bar = $full ? 'bg-red-600' : ($hot ? 'bg-gradient-to-r from-orange-400 to-red-500' : 'bg-gradient-to-r from-primary-400 to-primary-700');
        $ring = $full ? 'border-red-500' : ($hot ? 'border-orange-400' : 'border-gray-100');
        echo '<div class="bg-white rounded-2xl shadow-card border-2 ' . $ring . ' p-4 md:p-5 card-hover relative overflow-hidden">';
        if ($hot) echo '<span class="absolute top-2 right-2 text-red-500"><i class="fas fa-fire animate-pulse"></i></span>';
        echo '<div class="text-3xl md:text-4xl font-black text-gray-800 tracking-widest text-center">' . htmlspecialchars($r['number']) . '</div>';
        echo '<div class="mt-3 w-full bg-gray-100 rounded-full h-2.5 overflow-hidden"><div class="' . $bar . ' h-2.5 rounded-full transition-all" style="width:' . $pct . '%"></div></div>';
        echo '<div class="flex justify-between items-center mt-2 text-[11px] md:text-xs font-bold">';
        echo '<span class="text-gray-400">' . __('hot_filled') . '</span>';
        echo '<span class="' . ($full ? 'text-red-600' : ($hot ? 'text-orange-500' : 'text-primary')) . '">' . ($full ? __('hot_full') : $pct . '%') . '</span>';
        echo '</div></div>';
    }
    echo '</div>';
};
?>
<body class="w-full md:max-w-4xl lg:max-w-5xl mx-auto min-h-screen bg-gray-50 md:shadow-2xl md:border-x border-gray-200 transition-all duration-300 pb-24 md:pb-28">

    <div class="bg-brand-gradient text-white flex items-center p-4 md:p-6 sticky top-0 z-20 shadow-card">
        <a href="index.php" class="mr-4 text-xl md:text-2xl w-6 md:w-10 hover:scale-110 transition-transform"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl md:text-2xl font-bold flex-1 text-center pr-6 md:pr-10 tracking-wide"><?= __('title_hot_numbers') ?></h1>
    </div>

    <div class="px-4 md:px-8 pt-5 md:pt-7">
        <p class="text-gray-500 text-sm md:text-base font-medium mb-5 flex items-center">
            <i class="fas fa-fire text-red-500 mr-2 text-lg"></i><?= __('hot_numbers_subtitle') ?>
        </p>

        <div class="flex gap-2 mb-5 bg-white p-1.5 rounded-2xl shadow-sm border border-gray-100 w-full max-w-xs mx-auto">
            <button onclick="showHotTab('2d')" id="hot2dBtn" class="flex-1 py-2.5 rounded-xl font-bold text-sm md:text-base bg-brand-gradient text-white transition-all"><?= __('hot_2d') ?></button>
            <button onclick="showHotTab('3d')" id="hot3dBtn" class="flex-1 py-2.5 rounded-xl font-bold text-sm md:text-base text-gray-500 transition-all"><?= __('hot_3d') ?></button>
        </div>

        <div id="hot2d"><?php $render_hot($hot_2d, $threshold); ?></div>
        <div id="hot3d" class="hidden"><?php $render_hot($hot_3d, $threshold); ?></div>
    </div>

    <script>
        function showHotTab(t) {
            document.getElementById('hot2d').classList.toggle('hidden', t !== '2d');
            document.getElementById('hot3d').classList.toggle('hidden', t !== '3d');
            const on = 'flex-1 py-2.5 rounded-xl font-bold text-sm md:text-base bg-brand-gradient text-white transition-all';
            const off = 'flex-1 py-2.5 rounded-xl font-bold text-sm md:text-base text-gray-500 transition-all';
            document.getElementById('hot2dBtn').className = t === '2d' ? on : off;
            document.getElementById('hot3dBtn').className = t === '3d' ? on : off;
        }
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
