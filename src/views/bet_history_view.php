<?php require_once __DIR__ . '/../includes/header.php'; 

extract($summary_data);
$cancel_limit_minutes = $cancel_limit_seconds / 60;
?>

<body class="w-full md:max-w-3xl lg:max-w-5xl mx-auto min-h-screen bg-gray-50 md:shadow-2xl md:border-x border-gray-200 transition-all duration-300 pb-20 md:pb-24">

    <div class="bg-primary text-white flex items-center p-4 md:p-6 sticky top-0 z-20 shadow-md">
        <a href="index.php" class="mr-4 text-xl md:text-2xl w-6 md:w-10 hover:scale-110 transition-transform"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl md:text-2xl font-bold flex-1 text-center pr-6 md:pr-10 tracking-wide"><?= __('title_bet_history') ?></h1>
    </div>

    <div class="max-w-4xl mx-auto md:mt-4">

        <?php if (!empty($success_message)): ?>
            <div class="px-4 mt-4">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 md:py-4 rounded-xl relative text-sm md:text-base font-medium shadow-sm"><?= htmlspecialchars($success_message) ?></div>
            </div>
            <audio id="cancelSuccessSound" src="assets/sounds/notification.mp3" autoplay></audio>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var snd = document.getElementById('cancelSuccessSound');
                    if (snd) {
                        snd.play().catch(e => console.log("Autoplay prevented by browser."));
                    }
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                });
            </script>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="px-4 mt-4">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 md:py-4 rounded-xl relative text-sm md:text-base font-medium shadow-sm"><?= htmlspecialchars($error_message) ?></div>
            </div>
        <?php endif; ?>

        <div class="px-4 mt-4">
            <div class="bg-white rounded-2xl shadow-md p-4 md:p-6 border-t-4 hover:shadow-lg transition-shadow <?= $daily_profit >= 0 ? 'border-green-500' : 'border-red-500' ?>">
                <h3 class="font-bold text-gray-700 mb-4 md:mb-5 border-b pb-3 text-sm md:text-base flex items-center">
                    <i class="fas fa-chart-pie mr-2 md:mr-3 text-lg <?= $daily_profit >= 0 ? 'text-green-500' : 'text-red-500' ?>"></i> 
                    <?= ($summary_date == date('Y-m-d') ? __('today') : date('d-M-Y', strtotime($summary_date))) . __('daily_summary_suffix') ?>
                </h3>
                <div class="grid grid-cols-3 gap-4 md:gap-8 text-center mb-5 md:mb-6">
                    <div class="bg-blue-50/50 p-2 md:p-4 rounded-xl">
                        <p class="text-xs md:text-sm text-gray-500 font-medium">ထိုးကွက်</p>
                        <p class="font-bold text-blue-600 text-lg md:text-2xl mt-1"><?= number_format($daily_total_tickets) ?></p>
                    </div>
                    <div class="bg-green-50/50 p-2 md:p-4 rounded-xl">
                        <p class="text-xs md:text-sm text-gray-500 font-medium">ပေါက်ကွက်</p>
                        <p class="font-bold text-green-600 text-lg md:text-2xl mt-1"><?= number_format($daily_win_tickets) ?></p>
                    </div>
                    <div class="bg-red-50/50 p-2 md:p-4 rounded-xl">
                        <p class="text-xs md:text-sm text-gray-500 font-medium">ရှုံးကွက်</p>
                        <p class="font-bold text-red-500 text-lg md:text-2xl mt-1"><?= number_format($daily_lose_tickets) ?></p>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-3 md:p-5 rounded-xl border border-gray-200 space-y-2 md:space-y-3 shadow-inner">
                    <div class="flex justify-between items-center">
                        <span class="text-sm md:text-base text-gray-500 font-medium"><?= __('total_bet_amount') ?></span>
                        <span class="text-sm md:text-base font-bold text-red-500">- <?= number_format($daily_bet) ?> <?= __('currency') ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm md:text-base text-gray-500 font-medium"><?= __('total_win_amount') ?></span>
                        <span class="text-sm md:text-base font-bold text-green-600">+ <?= number_format($daily_win) ?> <?= __('currency') ?></span>
                    </div>
                    <div class="flex justify-between items-center border-t border-gray-200 pt-3 md:pt-4 mt-3 md:mt-4">
                        <span class="text-sm md:text-base font-bold text-gray-700 uppercase tracking-wide"><?= __('net_profit_loss') ?></span>
                        <span class="text-base md:text-xl font-bold <?= $daily_profit >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                        <?= $daily_profit > 0 ? '+' : '' ?><?= number_format($daily_profit) ?> <?= __('currency') ?>
                        </span>
                    </div>
                    <div class="mt-4 md:mt-5 text-center border-t border-gray-100 pt-4 md:pt-5">
                        <a href="export_history.php?date=<?= htmlspecialchars($summary_date) ?>" class="inline-block bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 hover:border-gray-300 text-xs md:text-sm font-bold py-2 md:py-2.5 px-4 md:px-6 rounded-lg shadow-sm transition-all duration-300 hover:-translate-y-0.5">
                            <i class="fas fa-file-excel mr-1.5 text-green-600"></i> <?= __('download_daily_history') ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-4 mt-4">
            <div class="bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100">
                <form method="GET" action="" class="flex flex-col md:flex-row gap-3 md:gap-4 items-end">
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                    
                    <div class="flex gap-2 w-full md:w-2/3">
                        <div class="w-1/2">
                            <label class="block text-xs font-bold text-gray-500 mb-1 ml-1"><?= __('search_number_placeholder') ?></label>
                            <input type="text" name="search_number" value="<?= htmlspecialchars($search_number) ?>" placeholder="ဥပမာ - 99" maxlength="3" class="w-full px-3 py-2.5 md:py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>
                        <div class="w-1/2">
                            <label class="block text-xs font-bold text-gray-500 mb-1 ml-1">ရက်စွဲ</label>
                            <input type="date" name="search_date" value="<?= htmlspecialchars($search_date) ?>" class="w-full px-3 py-2.5 md:py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all text-gray-700">
                        </div>
                    </div>

                    <div class="flex gap-2 w-full md:w-1/3">
                        <button type="submit" class="w-2/3 bg-primary text-white rounded-xl text-sm md:text-base py-2.5 md:py-3 font-bold shadow-md hover:bg-blue-800 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                            <i class="fas fa-search mr-1.5"></i> <?= __('search') ?>
                        </button>
                        <a href="bet_history.php" class="w-1/3 bg-gray-200 text-gray-700 rounded-xl text-sm md:text-base py-2.5 md:py-3 font-bold shadow-sm hover:bg-gray-300 transition-colors text-center flex justify-center items-center">
                            <i class="fas fa-sync-alt md:hidden"></i><span class="hidden md:inline"><?= __('clear_filter') ?></span>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <?php 
        $search_params = "";
        if(!empty($search_number)) $search_params .= "&search_number=".urlencode($search_number);
        if(!empty($search_date)) $search_params .= "&search_date=".urlencode($search_date);
        ?>

        <div class="px-4 mt-4">
            <div class="bg-white flex justify-around text-sm md:text-base font-bold text-gray-500 shadow-sm rounded-xl overflow-hidden border border-gray-100">
                <a href="?filter=all<?= $search_params ?>" class="py-3 md:py-4 w-1/4 text-center transition-colors <?= $filter == 'all' ? 'text-primary border-b-2 border-primary bg-blue-50/50' : 'hover:bg-gray-50 hover:text-primary' ?>"><?= __('all') ?></a>
                <a href="?filter=win<?= $search_params ?>" class="py-3 md:py-4 w-1/4 text-center transition-colors <?= $filter == 'win' ? 'text-green-600 border-b-2 border-green-600 bg-green-50/50' : 'hover:bg-gray-50 hover:text-green-600' ?>"><?= __('winning_numbers') ?></a>
                <a href="?filter=pending<?= $search_params ?>" class="py-3 md:py-4 w-1/4 text-center transition-colors <?= $filter == 'pending' ? 'text-yellow-600 border-b-2 border-yellow-600 bg-yellow-50/50' : 'hover:bg-gray-50 hover:text-yellow-600' ?>"><?= __('pending_bets') ?></a>
                <a href="?filter=lose<?= $search_params ?>" class="py-3 md:py-4 w-1/4 text-center transition-colors <?= $filter == 'lose' ? 'text-red-500 border-b-2 border-red-500 bg-red-50/50' : 'hover:bg-gray-50 hover:text-red-500' ?>"><?= __('losing_numbers') ?></a>
            </div>
        </div>

        <div class="p-4">
            <?php if (count($bets) > 0): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden md:bg-transparent md:border-none md:shadow-none md:space-y-3">
                    <?php foreach ($bets as $bet): ?>
                        <?php 
                            $voucher_id = strtoupper(substr($bet['voucher_id_hash'], 0, 8)); 
                        ?>
                        <div id="voucher_<?= $voucher_id ?>" class="border-b border-gray-100 last:border-b-0 p-4 md:p-5 flex justify-between items-center hover:bg-gray-50 bg-white md:rounded-xl md:border md:shadow-sm transition-all duration-200">
                            <div class="flex-1 pr-3">
                                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                    <span class="bg-gray-200 text-gray-700 text-[10px] md:text-xs px-2 md:px-2.5 py-0.5 md:py-1 rounded-md font-mono font-bold tracking-wider">#<?= $voucher_id ?></span>
                                    <p class="text-xs md:text-sm text-gray-500 font-medium"><i class="far fa-clock mr-1"></i> <?= date('d-M-Y h:i A', strtotime($bet['created_at'])) ?></p>
                                    
                                    <div class="flex items-center ml-1 md:ml-2 border-l border-gray-300 pl-2">
                                        <button onclick="showVoucherDetails('<?= $voucher_id ?>', '<?= $bet['created_at'] ?>')" class="text-blue-500 hover:text-blue-700 hover:bg-blue-50 p-1.5 rounded-md transition-colors" title="<?= __('view_details') ?>"><i class="fas fa-eye text-sm md:text-base"></i></button>
                                        <button onclick="downloadVoucher('<?= $voucher_id ?>')" class="text-gray-400 hover:text-blue-700 hover:bg-blue-50 p-1.5 rounded-md transition-colors ml-1 download-btn" title="<?= __('download_voucher') ?>"><i class="fas fa-download text-sm md:text-base"></i></button>
                                    </div>
                                </div>
                                <p class="font-bold text-lg md:text-xl text-gray-800">
                                    <span class="text-primary text-xl md:text-2xl mr-1"><?= htmlspecialchars($bet['total_kwek']) ?></span> <?= __('kwek') ?>
                                </p>
                                <p class="text-xs md:text-sm text-gray-500 mt-1 max-w-[220px] md:max-w-md break-words leading-relaxed font-medium">
                                    [<?= htmlspecialchars($bet['bet_numbers']) ?>]
                                </p>
                            </div>
                            <div class="text-right flex flex-col items-end">
                                <p class="text-xs md:text-sm text-gray-500 mb-1 font-medium"><?= __('total') ?></p>
                                <p class="font-bold text-red-600 text-base md:text-xl mb-2"><?= number_format($bet['total_amount']) ?> <span class="text-xs md:text-sm font-normal"><?= __('currency') ?></span></p>
                                
                                <?php if ($bet['win_count'] > 0): ?>
                                    <span class="bg-green-100 text-green-700 text-[10px] md:text-xs px-2.5 md:px-3 py-1 md:py-1.5 rounded-md border border-green-300 font-bold tracking-wide shadow-sm"><?= __('status_win') ?></span>
                                <?php elseif ($bet['pending_count'] > 0): ?>
                                    <span class="bg-yellow-100 text-yellow-700 text-[10px] md:text-xs px-2.5 md:px-3 py-1 md:py-1.5 rounded-md border border-yellow-300 font-bold tracking-wide shadow-sm"><?= __('status_pending') ?></span>
                                <?php else: ?>
                                    <span class="bg-red-100 text-red-700 text-[10px] md:text-xs px-2.5 md:px-3 py-1 md:py-1.5 rounded-md border border-red-300 font-bold tracking-wide shadow-sm"><?= __('status_lose') ?></span>
                                <?php endif; ?>
                                
                                <?php 
                                if ($cancel_limit_seconds > 0 && $bet['pending_count'] > 0 && $bet['win_count'] == 0 && (time() - strtotime($bet['created_at'])) <= $cancel_limit_seconds): ?>
                                    <form method="POST" class="mt-3" onsubmit="confirmCancel(event)">
                                        <input type="hidden" name="action" value="cancel_bet">
                                        <input type="hidden" name="voucher_id" value="<?= $voucher_id ?>">
                                        <input type="hidden" name="created_at" value="<?= $bet['created_at'] ?>">
                                        <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-500 hover:text-white border border-red-200 hover:border-red-500 text-[10px] md:text-xs font-bold px-3 py-1.5 rounded-md shadow-sm transition-all duration-300">
                                            <i class="fas fa-undo mr-1"></i> <?= __('btn_cancel_bet') ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-2xl shadow-md p-10 md:p-16 text-center mt-4 border border-gray-100">
                    <div class="text-gray-200 mb-5 animate-pulse">
                        <i class="fas fa-receipt text-7xl md:text-8xl"></i>
                    </div>
                    <p class="text-gray-500 font-bold text-sm md:text-lg mb-6"><?= __('no_records_found') ?></p>
                    <a href="2d_bet.php" class="inline-block bg-primary text-white px-8 md:px-10 py-3 md:py-3.5 rounded-xl text-sm md:text-base font-bold shadow-md hover:bg-blue-800 hover:shadow-lg transition-all hover:-translate-y-0.5">
                        <i class="fas fa-play-circle mr-2"></i> <?= __('go_to_2d_bet') ?>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($total_pages > 1): ?>
                <div class="flex justify-center items-center mt-8 md:mt-10 mb-4 space-x-2 md:space-x-3">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&filter=<?= htmlspecialchars($filter) ?><?= $search_params ?>" class="px-4 md:px-5 py-2 md:py-2.5 bg-white border border-gray-300 rounded-lg md:rounded-xl text-gray-600 hover:bg-gray-50 hover:text-primary hover:border-primary shadow-sm transition-all"><i class="fas fa-chevron-left text-xs md:text-sm"></i></a>
                    <?php endif; ?>
                    
                    <span class="px-4 md:px-6 py-2 md:py-2.5 bg-white border border-gray-300 rounded-lg md:rounded-xl text-sm md:text-base font-bold text-gray-700 shadow-sm"><?= __('page') ?> <?= $page ?> / <?= $total_pages ?></span>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>&filter=<?= htmlspecialchars($filter) ?><?= $search_params ?>" class="px-4 md:px-5 py-2 md:py-2.5 bg-white border border-gray-300 rounded-lg md:rounded-xl text-gray-600 hover:bg-gray-50 hover:text-primary hover:border-primary shadow-sm transition-all"><i class="fas fa-chevron-right text-xs md:text-sm"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div> <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function confirmCancel(event) {
            event.preventDefault();
            const form = event.target;

            Swal.fire({
                title: '<?= __('confirm_cancel_bet') ?>',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<?= __('delete') ?>',
                cancelButtonText: '<?= __('cancel') ?>'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
        function showVoucherDetails(voucherId, createdAt) {
            Swal.fire({
                title: '<?= __('loading_details') ?>',
                text: 'ခေတ္တစောင့်ဆိုင်းပါ...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`bet_history.php?action=get_voucher_details&created_at=${encodeURIComponent(createdAt)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let detailsHtml = `<div class="text-left max-h-80 overflow-y-auto"><table class="w-full text-sm"><thead><tr><th>Number</th><th>Amount</th><th>Status</th></tr></thead><tbody>`;
                        data.details.forEach(bet => {
                            detailsHtml += `<tr><td>${bet.bet_number}</td><td>${bet.amount}</td><td>${bet.status}</td></tr>`;
                        });
                        detailsHtml += `</tbody></table></div>`;
                        Swal.fire({
                            title: `Voucher #${voucherId}`,
                            html: detailsHtml,
                            confirmButtonText: 'Close'
                        });
                    } else {
                        Swal.fire('Error', 'Could not fetch details.', 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error', 'An error occurred.', 'error');
                });
        }
        function downloadVoucher(voucherId) {
            const element = document.getElementById('voucher_' + voucherId);
            html2canvas(element).then(canvas => {
                const link = document.createElement('a');
                link.download = 'voucher_' + voucherId + '.png';
                link.href = canvas.toDataURL();
                link.click();
            });
        }
    </script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
