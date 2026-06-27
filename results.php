<?php
require_once __DIR__ . '/core/auth_check.php';
require_once __DIR__ . '/core/db_connect.php';
require_once __DIR__ . '/lang/language.php';
date_default_timezone_set('Asia/Yangon');

$results_2d = [];
$results_3d = [];
$res = $conn->query("SELECT result_number, type, created_at FROM result_history ORDER BY created_at DESC LIMIT 80");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        if ($r['type'] === '3D') $results_3d[] = $r;
        else $results_2d[] = $r;
    }
}

$video_row = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'results_video_url'");
$video_url = ($video_row && $vr = $video_row->fetch_assoc()) ? trim($vr['setting_value']) : '';

function results_video_embed($url) {
    $url = trim($url);
    if ($url === '') return '';
    if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})#', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    return $url;
}
$embed_url = results_video_embed($video_url);

$page_title = __('title_results') . " - Thai 2D3D";
require_once __DIR__ . '/includes/header.php';
?>
<body class="w-full md:max-w-4xl lg:max-w-5xl mx-auto min-h-screen bg-gray-50 md:shadow-2xl md:border-x border-gray-200 transition-all duration-300 pb-24 md:pb-28">

    <div class="bg-brand-gradient text-white flex items-center p-4 md:p-6 sticky top-0 z-20 shadow-card">
        <a href="index.php" class="mr-4 text-xl md:text-2xl w-6 md:w-10 hover:scale-110 transition-transform"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl md:text-2xl font-bold flex-1 text-center pr-6 md:pr-10 tracking-wide"><?= __('title_results') ?></h1>
    </div>

    <div class="px-4 md:px-8 pt-5 md:pt-7">
        <p class="text-gray-500 text-sm md:text-base font-medium mb-5 flex items-center">
            <i class="fas fa-trophy text-gold-500 mr-2 text-lg"></i><?= __('results_subtitle') ?>
        </p>

        <div class="bg-white rounded-3xl shadow-card border border-gray-100 overflow-hidden mb-6">
            <div class="bg-red-500/90 text-white px-5 py-3 flex items-center font-bold text-sm md:text-base">
                <i class="fas fa-video mr-2 animate-pulse"></i> <?= __('results_live_video') ?>
            </div>
            <?php if ($embed_url !== ''): ?>
                <div class="relative w-full" style="padding-top:56.25%">
                    <iframe src="<?= htmlspecialchars(safe_url($embed_url)) ?>" class="absolute inset-0 w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            <?php else: ?>
                <div class="p-10 text-center text-gray-400">
                    <i class="fas fa-tv text-5xl mb-3 opacity-40"></i>
                    <p class="font-bold text-sm md:text-base"><?= __('results_video_none') ?></p>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex gap-2 mb-5 bg-white p-1.5 rounded-2xl shadow-sm border border-gray-100 w-full max-w-xs mx-auto">
            <button onclick="showResultTab('2d')" id="tab2dBtn" class="result-tab flex-1 py-2.5 rounded-xl font-bold text-sm md:text-base bg-brand-gradient text-white transition-all"><?= __('results_2d') ?></button>
            <button onclick="showResultTab('3d')" id="tab3dBtn" class="result-tab flex-1 py-2.5 rounded-xl font-bold text-sm md:text-base text-gray-500 transition-all"><?= __('results_3d') ?></button>
        </div>

        <?php
        $render = function ($rows) {
            if (count($rows) === 0) {
                echo '<div class="bg-white rounded-3xl shadow-card border border-gray-100 p-10 text-center text-gray-400"><i class="fas fa-inbox text-5xl mb-3 opacity-40"></i><p class="font-bold text-sm md:text-base">' . __('results_none') . '</p></div>';
                return;
            }
            echo '<div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">';
            foreach ($rows as $r) {
                echo '<div class="bg-white rounded-2xl shadow-card border border-gray-100 p-4 md:p-5 text-center card-hover">';
                echo '<div class="text-3xl md:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-b from-primary-600 to-primary-900 tracking-widest">' . htmlspecialchars($r['result_number']) . '</div>';
                echo '<div class="text-[11px] md:text-xs text-gray-400 font-semibold mt-2"><i class="far fa-calendar mr-1"></i>' . date('d M Y', strtotime($r['created_at'])) . '</div>';
                echo '<div class="text-[11px] md:text-xs text-gray-400 font-medium">' . date('h:i A', strtotime($r['created_at'])) . '</div>';
                echo '</div>';
            }
            echo '</div>';
        };
        ?>
        <div id="tab2d" class="result-panel"><?php $render($results_2d); ?></div>
        <div id="tab3d" class="result-panel hidden"><?php $render($results_3d); ?></div>
    </div>

    <script>
        function showResultTab(t) {
            document.getElementById('tab2d').classList.toggle('hidden', t !== '2d');
            document.getElementById('tab3d').classList.toggle('hidden', t !== '3d');
            const on = 'bg-brand-gradient text-white';
            const off = 'text-gray-500';
            const b2 = document.getElementById('tab2dBtn'), b3 = document.getElementById('tab3dBtn');
            b2.className = 'result-tab flex-1 py-2.5 rounded-xl font-bold text-sm md:text-base transition-all ' + (t === '2d' ? on : off);
            b3.className = 'result-tab flex-1 py-2.5 rounded-xl font-bold text-sm md:text-base transition-all ' + (t === '3d' ? on : off);
        }
    </script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
