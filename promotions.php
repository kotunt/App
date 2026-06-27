<?php
require_once __DIR__ . '/core/auth_check.php';
require_once __DIR__ . '/core/db_connect.php';
require_once __DIR__ . '/lang/language.php';

$promos = [];
$promo_res = $conn->query("SELECT title, description, image_url, badge, link_url FROM promotions WHERE is_active = 1 ORDER BY sort_order ASC, id DESC");
if ($promo_res) {
    $promos = $promo_res->fetch_all(MYSQLI_ASSOC);
}

$page_title = __('title_promotions') . " - Thai 2D3D";
require_once __DIR__ . '/includes/header.php';
?>
<body class="w-full md:max-w-4xl lg:max-w-5xl mx-auto min-h-screen bg-gray-50 md:shadow-2xl md:border-x border-gray-200 transition-all duration-300 pb-24 md:pb-28">

    <div class="bg-brand-gradient text-white flex items-center p-4 md:p-6 sticky top-0 z-20 shadow-card">
        <a href="index.php" class="mr-4 text-xl md:text-2xl w-6 md:w-10 hover:scale-110 transition-transform"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl md:text-2xl font-bold flex-1 text-center pr-6 md:pr-10 tracking-wide"><?= __('title_promotions') ?></h1>
    </div>

    <div class="px-4 md:px-8 pt-5 md:pt-7">
        <p class="text-gray-500 text-sm md:text-base font-medium mb-5 flex items-center">
            <i class="fas fa-gift text-gold-500 mr-2 text-lg"></i><?= __('promotions_subtitle') ?>
        </p>

        <?php if (count($promos) === 0): ?>
            <div class="bg-white rounded-3xl shadow-card border border-gray-100 p-10 text-center text-gray-400 mt-6">
                <i class="fas fa-box-open text-5xl mb-4 opacity-40"></i>
                <p class="font-bold text-sm md:text-base"><?= __('no_promotions') ?></p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <?php foreach ($promos as $p): ?>
                    <div class="bg-white rounded-3xl shadow-card border border-gray-100 overflow-hidden card-hover animate-fadeUp flex flex-col">
                        <?php if (!empty($p['image_url'])): ?>
                            <div class="h-40 md:h-48 w-full overflow-hidden relative">
                                <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['title']) ?>" class="w-full h-full object-cover">
                                <?php if (!empty($p['badge'])): ?>
                                    <span class="absolute top-3 left-3 bg-gold-gradient text-primary-900 text-[10px] md:text-xs font-extrabold px-3 py-1 rounded-full shadow-gold"><?= htmlspecialchars($p['badge']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="h-28 md:h-32 w-full bg-brand-gradient relative flex items-center justify-center">
                                <i class="fas fa-bullhorn text-white/30 text-6xl"></i>
                                <?php if (!empty($p['badge'])): ?>
                                    <span class="absolute top-3 left-3 bg-gold-gradient text-primary-900 text-[10px] md:text-xs font-extrabold px-3 py-1 rounded-full shadow-gold"><?= htmlspecialchars($p['badge']) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="p-5 md:p-6 flex-1 flex flex-col">
                            <h2 class="font-bold text-gray-800 text-base md:text-lg mb-2 leading-snug"><?= htmlspecialchars($p['title']) ?></h2>
                            <?php if (!empty($p['description'])): ?>
                                <p class="text-sm md:text-base text-gray-500 leading-relaxed flex-1"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($p['link_url'])): ?>
                                <a href="<?= htmlspecialchars(safe_url($p['link_url'])) ?>" class="mt-4 inline-flex items-center justify-center bg-brand-gradient text-white font-bold text-sm md:text-base py-2.5 md:py-3 rounded-2xl shadow-card hover:-translate-y-0.5 transition-all">
                                    <?= __('promo_view_detail') ?> <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
