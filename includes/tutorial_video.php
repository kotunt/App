<?php
/**
 * Reusable how-to tutorial video button + modal.
 * Expects before include:
 *   $tv_url   (string) raw video url (youtube watch/short/embed or direct)
 *   $tv_label (string) button label
 *   $tv_id    (string) unique DOM id for the modal
 */
if (!function_exists('tutorial_video_embed')) {
    function tutorial_video_embed($url) {
        $url = trim($url);
        if ($url === '') return '';
        if (preg_match('#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        return $url;
    }
}
$tv_url = trim($tv_url ?? '');
if ($tv_url !== ''):
    $tv_embed = tutorial_video_embed($tv_url);
    if (function_exists('safe_url')) { $tv_embed = safe_url($tv_embed); }
    $tv_id = $tv_id ?? 'tutorialVideoModal';
    $tv_label = $tv_label ?? __('watch_video');
?>
<div class="mb-5">
    <button type="button" onclick="document.getElementById('<?= htmlspecialchars($tv_id) ?>').classList.remove('hidden')"
        class="w-full flex items-center justify-center gap-2 bg-red-50 text-red-600 border border-red-200 font-bold py-3 md:py-3.5 rounded-2xl text-sm md:text-base hover:bg-red-100 transition-colors shadow-sm">
        <i class="fas fa-circle-play text-lg md:text-xl"></i> <?= htmlspecialchars($tv_label) ?>
    </button>
</div>
<div id="<?= htmlspecialchars($tv_id) ?>" class="fixed inset-0 bg-black/80 z-[70] hidden flex items-center justify-center p-4" onclick="if(event.target===this){this.classList.add('hidden');}">
    <div class="bg-black rounded-2xl overflow-hidden w-full max-w-2xl relative shadow-2xl">
        <button type="button" onclick="document.getElementById('<?= htmlspecialchars($tv_id) ?>').classList.add('hidden');"
            class="absolute top-2 right-2 z-10 w-9 h-9 bg-white/90 rounded-full flex items-center justify-center text-gray-800 shadow"><i class="fas fa-times"></i></button>
        <div class="relative w-full" style="padding-top:56.25%">
            <iframe src="<?= htmlspecialchars($tv_embed) ?>" class="absolute inset-0 w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </div>
</div>
<?php endif; ?>
