<?php
/**
 * Reusable Dream Book (အိပ်မက်ဂဏန်း) button + modal for the bet flow.
 * Expects before include:
 *   $dream_book (array) rows with title, icon, number_2d, number_3d
 *   $dream_mode (string) '2d' or '3d' (which number column to surface)
 * Appends tapped numbers into the #bet_number textarea and re-syncs the grid.
 */
$dream_mode = ($dream_mode ?? '2d') === '3d' ? '3d' : '2d';
$dream_col  = $dream_mode === '3d' ? 'number_3d' : 'number_2d';
$dream_len  = $dream_mode === '3d' ? 3 : 2;
if (!empty($dream_book)):
?>
<div class="px-4 mb-4">
    <button type="button" onclick="document.getElementById('dreamBookModal').classList.remove('hidden')"
        class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-purple-500 to-fuchsia-600 text-white font-bold py-3 rounded-2xl text-sm md:text-base shadow-md hover:-translate-y-0.5 transition-all active:scale-95">
        <i class="fas fa-moon text-lg"></i> <?= __('dream_book_title') ?>
    </button>
</div>

<div id="dreamBookModal" class="fixed inset-0 bg-black/70 z-[80] hidden flex items-end md:items-center justify-center md:p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white dark:bg-slate-800 w-full md:max-w-lg md:rounded-3xl rounded-t-3xl shadow-2xl max-h-[85vh] flex flex-col overflow-hidden">
        <div class="bg-gradient-to-r from-purple-500 to-fuchsia-600 text-white p-4 flex items-center justify-between sticky top-0">
            <h3 class="font-bold text-lg flex items-center gap-2"><i class="fas fa-moon"></i> <?= __('dream_book_title') ?></h3>
            <button type="button" onclick="document.getElementById('dreamBookModal').classList.add('hidden')" class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-3 border-b border-slate-100 dark:border-slate-700">
            <input type="text" id="dreamSearch" onkeyup="filterDream()" placeholder="<?= __('dream_search') ?>"
                class="w-full py-2.5 px-4 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-400 text-sm">
        </div>
        <div id="dreamList" class="overflow-y-auto p-3 space-y-2 custom-scrollbar">
            <?php foreach ($dream_book as $d): ?>
                <?php
                    $nums = array_filter(array_map('trim', preg_split('/[^0-9]+/', (string)($d[$dream_col] ?? ''))));
                ?>
                <div class="dream-item flex items-center gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700" data-title="<?= htmlspecialchars(mb_strtolower($d['title'])) ?>">
                    <div class="text-2xl w-9 text-center"><?= htmlspecialchars($d['icon'] ?? '✨') ?></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-700 dark:text-slate-200 text-sm mb-1 truncate"><?= htmlspecialchars($d['title']) ?></p>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach ($nums as $n): ?>
                                <button type="button" onclick="addDreamNum('<?= htmlspecialchars($n) ?>')"
                                    class="bg-white dark:bg-slate-800 border border-purple-200 dark:border-purple-700 text-purple-600 dark:text-purple-300 font-bold text-xs px-2.5 py-1 rounded-lg hover:bg-purple-50 active:scale-95 transition-all"><?= htmlspecialchars($n) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <p id="dreamEmpty" class="hidden text-center text-slate-400 text-sm py-6"><?= __('no_data') ?></p>
        </div>
    </div>
</div>
<script>
(function(){
    window.filterDream = function(){
        var q = (document.getElementById('dreamSearch').value || '').toLowerCase();
        var any = false;
        document.querySelectorAll('#dreamList .dream-item').forEach(function(it){
            var match = it.getAttribute('data-title').indexOf(q) !== -1;
            it.style.display = match ? '' : 'none';
            if (match) any = true;
        });
        document.getElementById('dreamEmpty').classList.toggle('hidden', any);
    };
    window.addDreamNum = function(num){
        var ta = document.getElementById('bet_number');
        if (!ta) return;
        var len = <?= $dream_len ?>;
        var nums = (ta.value.match(/[0-9]+/g) || []).filter(function(x){ return x.length === len; });
        if (nums.indexOf(num) === -1) { nums.push(num); }
        ta.value = nums.join(', ');
        if (typeof syncGrid === 'function') { try { syncGrid(); } catch(e){} }
    };
})();
</script>
<?php endif; ?>
