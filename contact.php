<?php
require_once __DIR__ . '/core/auth_check.php';
require_once __DIR__ . '/core/db_connect.php';
require_once __DIR__ . '/lang/language.php';

$keys = ['cs_telegram_link', 'cs_viber_link', 'cs_messenger_link', 'cs_phone', 'cs_channel_link'];
$cs = array_fill_keys($keys, '');
$in = "'" . implode("','", $keys) . "'";
$cs_res = $conn->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ($in)");
if ($cs_res) {
    while ($r = $cs_res->fetch_assoc()) {
        $cs[$r['setting_key']] = trim($r['setting_value']);
    }
}

$norm = function ($url) {
    $url = trim($url);
    if ($url === '') return '';
    if (preg_match('#^(https?:|viber:|tel:|mailto:)#i', $url)) return $url;
    return 'https://' . ltrim($url, '/');
};

$methods = [];
if (!empty($cs['cs_telegram_link'])) {
    $methods[] = ['icon' => 'fab fa-telegram-plane', 'bg' => 'bg-sky-500', 'label' => __('contact_telegram'), 'href' => $norm($cs['cs_telegram_link']), 'cta' => __('contact_open')];
}
if (!empty($cs['cs_viber_link'])) {
    $methods[] = ['icon' => 'fab fa-viber', 'bg' => 'bg-purple-600', 'label' => __('contact_viber'), 'href' => $norm($cs['cs_viber_link']), 'cta' => __('contact_open')];
}
if (!empty($cs['cs_messenger_link'])) {
    $methods[] = ['icon' => 'fab fa-facebook-messenger', 'bg' => 'bg-blue-500', 'label' => __('contact_messenger'), 'href' => $norm($cs['cs_messenger_link']), 'cta' => __('contact_open')];
}
if (!empty($cs['cs_channel_link'])) {
    $methods[] = ['icon' => 'fas fa-bullhorn', 'bg' => 'bg-emerald-500', 'label' => __('contact_channel'), 'href' => $norm($cs['cs_channel_link']), 'cta' => __('contact_open')];
}
if (!empty($cs['cs_phone'])) {
    $methods[] = ['icon' => 'fas fa-phone-alt', 'bg' => 'bg-rose-500', 'label' => __('contact_phone') . ' — ' . htmlspecialchars($cs['cs_phone']), 'href' => 'tel:' . preg_replace('/[^0-9+]/', '', $cs['cs_phone']), 'cta' => __('contact_call_now')];
}

$page_title = __('title_contact') . " - Thai 2D3D";
require_once __DIR__ . '/includes/header.php';
?>
<body class="w-full md:max-w-4xl lg:max-w-5xl mx-auto min-h-screen bg-gray-50 md:shadow-2xl md:border-x border-gray-200 transition-all duration-300 pb-24 md:pb-28">

    <div class="bg-brand-gradient text-white flex items-center p-4 md:p-6 sticky top-0 z-20 shadow-card">
        <a href="index.php" class="mr-4 text-xl md:text-2xl w-6 md:w-10 hover:scale-110 transition-transform"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl md:text-2xl font-bold flex-1 text-center pr-6 md:pr-10 tracking-wide"><?= __('title_contact') ?></h1>
    </div>

    <div class="px-4 md:px-8 pt-6 md:pt-8">
        <div class="bg-brand-gradient text-white rounded-3xl shadow-premium p-6 md:p-8 text-center relative overflow-hidden mb-6">
            <div class="absolute top-[-30%] right-[-10%] w-48 h-48 bg-gold-400/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="w-16 h-16 md:w-20 md:h-20 bg-white/15 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                <i class="fas fa-headset text-3xl md:text-4xl text-gold-400"></i>
            </div>
            <h2 class="font-bold text-lg md:text-2xl mb-1"><?= __('contact_subtitle') ?></h2>
            <p class="text-xs md:text-sm opacity-80"><?= __('contact_hours') ?></p>
        </div>

        <?php if (count($methods) === 0): ?>
            <div class="bg-white rounded-3xl shadow-card border border-gray-100 p-10 text-center text-gray-400">
                <i class="fas fa-comment-slash text-5xl mb-4 opacity-40"></i>
                <p class="font-bold text-sm md:text-base"><?= __('contact_none') ?></p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                <?php foreach ($methods as $m): ?>
                    <a href="<?= htmlspecialchars($m['href']) ?>" target="_blank" rel="noopener noreferrer"
                       class="group bg-white p-4 md:p-5 rounded-2xl shadow-card border border-gray-100 flex items-center hover:-translate-y-0.5 transition-all duration-300">
                        <div class="w-12 h-12 md:w-14 md:h-14 <?= $m['bg'] ?> rounded-2xl flex items-center justify-center text-white shadow-md flex-shrink-0">
                            <i class="<?= $m['icon'] ?> text-2xl md:text-3xl"></i>
                        </div>
                        <div class="flex-1 ml-4">
                            <p class="font-bold text-gray-800 md:text-lg group-hover:text-primary transition-colors"><?= $m['label'] ?></p>
                            <p class="text-xs md:text-sm text-primary font-semibold mt-0.5"><?= $m['cta'] ?> <i class="fas fa-arrow-right ml-1 text-[10px] group-hover:translate-x-1 transition-transform inline-block"></i></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
