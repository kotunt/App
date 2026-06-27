<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<body class="w-full md:max-w-3xl lg:max-w-5xl mx-auto min-h-screen bg-gray-50 md:shadow-2xl md:border-x border-gray-200 transition-all duration-300 pb-20 md:pb-24">

    <div class="bg-brand-gradient text-white flex items-center p-4 md:p-6 sticky top-0 z-20 shadow-premium relative overflow-hidden">
        <div class="absolute top-[-50%] right-[-5%] w-40 h-40 bg-gold-400/10 rounded-full blur-2xl pointer-events-none"></div>
        <a href="index.php" class="mr-4 text-xl md:text-2xl w-6 md:w-10 hover:scale-110 transition-transform relative z-10"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl md:text-2xl font-bold flex-1 text-center tracking-wide relative z-10"><?= __('title_deposit') ?></h1>
        <a href="transaction_history.php?type=deposit" class="ml-4 text-lg md:text-xl w-6 md:w-10 text-right hover:scale-110 transition-transform relative z-10" title="<?= __('view_history') ?>"><i class="fas fa-history"></i></a>
    </div>

    <div class="p-4 md:p-8 max-w-2xl mx-auto">
        <?php if (isset($success_message) && $step === 3): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 md:py-4 rounded-2xl relative mb-5 text-sm md:text-base font-medium shadow-card flex items-center gap-2">
                <i class="fas fa-circle-check"></i>
                <span><?= htmlspecialchars($success_message) ?></span>
            </div>
        <?php endif; ?>
        <?php if (isset($error_message) && $step !== 3): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 md:py-4 rounded-2xl relative mb-5 text-sm md:text-base font-medium shadow-card flex items-center gap-2">
                <i class="fas fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($error_message) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <form method="POST" action="deposit.php" class="bg-white p-6 md:p-10 rounded-3xl shadow-card border border-gray-100">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="step1">
                <h2 class="text-lg md:text-xl font-bold text-gray-800 mb-5 md:mb-6 border-b border-gray-100 pb-4 flex items-center">
                    <span class="w-9 h-9 md:w-10 md:h-10 rounded-2xl bg-brand-gradient text-white flex items-center justify-center mr-3 shadow-card"><i class="fas fa-wallet"></i></span>
                    <?= __('choose_payment_method') ?>
                </h2>

                <?php if (count($payment_accounts) > 0): ?>
                    <div class="grid grid-cols-2 md:grid-cols-2 gap-3 md:gap-5 mb-6">
                        <?php foreach($payment_accounts as $acc): ?>
                        <label class="cursor-pointer relative group">
                            <input type="radio" name="payment_method" value="<?= htmlspecialchars($acc['payment_method']) ?>" class="peer hidden" required <?= (isset($_SESSION['deposit_data']['payment_method']) && $_SESSION['deposit_data']['payment_method'] === $acc['payment_method']) ? 'checked' : '' ?>>
                            <div class="border border-gray-200 rounded-2xl p-3 md:p-4 flex flex-col items-center gap-2 group-hover:bg-primary-50 group-hover:border-primary-300 peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:shadow-card transition-all duration-300">
                                <?php if(!empty($acc['logo_url'])): ?>
                                    <img src="<?= htmlspecialchars($acc['logo_url']) ?>" class="w-10 h-10 md:w-12 md:h-12 object-cover rounded-full shadow-sm group-hover:scale-105 transition-transform">
                                <?php else: ?>
                                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-primary-100 text-primary flex items-center justify-center text-lg md:text-xl group-hover:scale-105 transition-transform"><i class="fas fa-university"></i></div>
                                <?php endif; ?>
                                <span class="text-sm md:text-base font-bold text-gray-700 text-center"><?= htmlspecialchars($acc['payment_method']) ?></span>
                                <div class="absolute top-2 right-2 text-primary hidden peer-checked:block"><i class="fas fa-check-circle md:text-lg"></i></div>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="mb-6 md:mb-8">
                        <label class="block text-gray-700 text-sm md:text-base font-bold mb-2 md:mb-3 ml-1"><?= __('deposit_amount_ks') ?></label>
                        <input type="number" name="amount" min="<?= $min_deposit ?>" max="<?= $max_deposit ?>" value="<?= htmlspecialchars($_SESSION['deposit_data']['amount'] ?? '') ?>" placeholder="<?= str_replace('%amount%', number_format($min_deposit), __('min_deposit_placeholder')) ?>" class="w-full py-3.5 md:py-4 px-4 rounded-2xl border border-gray-200 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 text-lg md:text-xl font-bold transition-all" required>
                    </div>

                    <button type="submit" class="w-full bg-brand-gradient hover:shadow-premium text-white font-bold py-3.5 md:py-4 rounded-2xl text-lg md:text-xl shadow-card hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
                        <?= __('continue') ?> <i class="fas fa-arrow-right"></i>
                    </button>
                <?php else: ?>
                    <p class="text-sm md:text-base text-red-500 italic text-center py-6 md:py-8"><?= __('no_payment_accounts_available') ?></p>
                <?php endif; ?>
            </form>

        <?php elseif ($step === 2): 
            $selected_account = null;
            foreach ($payment_accounts as $acc) {
                if ($acc['payment_method'] === $_SESSION['deposit_data']['payment_method']) {
                    $selected_account = $acc; break;
                }
            }
        ?>
            <form method="POST" action="deposit.php" enctype="multipart/form-data" class="bg-white p-6 md:p-10 rounded-3xl shadow-card border border-gray-100">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="action" value="step2">
                
                <div class="text-center mb-5 md:mb-6 border-b border-gray-100 pb-5 md:pb-6">
                    <p class="text-sm md:text-base text-gray-500 font-bold mb-1 md:mb-2"><?= __('your_deposit_amount') ?></p>
                    <p class="text-3xl md:text-4xl font-extrabold text-gold-gradient tracking-tight"><?= number_format($_SESSION['deposit_data']['amount']) ?> <span class="text-xl md:text-2xl"><?= __('currency') ?></span></p>
                </div>

                <div class="bg-primary-50 border border-primary-200 rounded-3xl p-5 md:p-6 mb-6 text-center relative overflow-hidden shadow-soft">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-brand-gradient"></div>
                    <p class="text-xs md:text-sm text-primary-800 font-bold mb-3 md:mb-4 uppercase tracking-widest"><?= __('transfer_to_account') ?></p>
                    
                    <div class="flex justify-center items-center gap-2 md:gap-3 mb-2 md:mb-3">
                        <?php if(!empty($selected_account['logo_url'])): ?>
                            <img src="<?= htmlspecialchars($selected_account['logo_url']) ?>" class="w-7 h-7 md:w-8 md:h-8 rounded-full object-cover shadow-sm">
                        <?php endif; ?>
                        <span class="font-bold text-gray-800 md:text-lg"><?= htmlspecialchars($selected_account['payment_method']) ?></span>
                    </div>
                    
                    <p class="text-2xl md:text-3xl font-bold text-primary tracking-wider mb-2" id="accNumber"><?= htmlspecialchars($selected_account['account_number']) ?></p>
                    <button type="button" onclick="copyAccNumber()" class="text-xs md:text-sm bg-white border border-gray-200 px-4 py-1.5 rounded-xl shadow-sm hover:bg-primary-50 hover:text-primary hover:border-primary-300 transition-colors text-gray-600 mb-2 md:mb-3"><i class="fas fa-copy mr-1.5"></i> <?= __('copy') ?></button>
                    
                    <p class="text-sm md:text-base text-gray-600 mt-2 font-bold"><?= htmlspecialchars($selected_account['account_name']) ?></p>

                    <?php if (!empty($selected_account['qr_image_url'])): ?>
                        <div class="mt-5 border-t border-primary-200 pt-5 flex justify-center">
                            <img src="<?= htmlspecialchars($selected_account['qr_image_url']) ?>" alt="QR" class="w-32 h-32 md:w-40 md:h-40 rounded-2xl border-2 border-gray-200 shadow-card object-cover hover:scale-105 transition-transform">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-5 md:mb-6">
                    <label class="block text-gray-700 text-sm md:text-base font-bold mb-2 md:mb-3 ml-1"><?= __('transaction_id') ?></label>
                    <input type="text" name="transaction_id" placeholder="<?= __('transaction_id_placeholder') ?>" class="w-full py-3.5 md:py-4 px-4 rounded-2xl border border-gray-200 bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition-all md:text-lg" required>
                    <p class="text-[10px] md:text-xs text-gray-500 mt-1.5 ml-1 font-medium"><?= __('transaction_id_help') ?></p>
                </div>
                
                <div class="mb-6 md:mb-8">
                    <label class="block text-gray-700 text-sm md:text-base font-bold mb-2 md:mb-3 ml-1"><?= __('upload_slip_image') ?></label>
                    <input type="file" name="slip_image" accept="image/png, image/jpeg, image/jpg, image/webp" class="w-full text-sm md:text-base text-gray-500 rounded-2xl border border-gray-200 p-2 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary hover:file:bg-primary-100 transition-colors cursor-pointer" required>
                </div>

                <div class="flex gap-3 md:gap-4">
                    <button type="submit" name="back" value="1" formnovalidate class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3.5 md:py-4 rounded-2xl shadow-sm hover:shadow-card transition-all duration-300 text-sm md:text-base">
                        <i class="fas fa-arrow-left mr-1"></i> <?= __('back') ?>
                    </button>
                    <button type="submit" class="w-2/3 bg-brand-gradient hover:shadow-premium text-white font-bold py-3.5 md:py-4 rounded-2xl text-lg md:text-xl shadow-card hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
                        <?= __('confirm') ?> <i class="fas fa-check-circle"></i>
                    </button>
                </div>
                <script>
                    function copyAccNumber() {
                        var copyText = document.getElementById("accNumber").innerText;
                        navigator.clipboard.writeText(copyText);
                        Swal.fire({
                            icon: 'success',
                            title: '<?= __('success') ?>',
                            text: '<?= __('account_copied_success') ?>',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            customClass: {
                                popup: 'rounded-2xl'
                            }
                        });
                    }
                </script>
            </form>

        <?php elseif ($step === 3): ?>
            <div class="bg-white p-8 md:p-12 rounded-3xl shadow-card border border-gray-100 text-center">
                <div class="w-24 h-24 md:w-28 md:h-28 mx-auto mb-5 rounded-full bg-green-50 flex items-center justify-center">
                    <div class="text-green-500 text-5xl md:text-6xl animate-bounce"><i class="fas fa-check-circle"></i></div>
                </div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-gray-800 mb-3"><?= __('success') ?></h2>
                <p class="text-sm md:text-base text-gray-600 mb-8 leading-relaxed font-medium"><?= htmlspecialchars($success_message) ?></p>
                <a href="index.php" class="inline-flex items-center justify-center gap-2 w-full md:w-auto md:px-12 bg-brand-gradient hover:shadow-premium text-white font-bold py-3.5 md:py-4 rounded-2xl text-lg shadow-card hover:-translate-y-0.5 transition-all duration-300"><i class="fas fa-house"></i> <?= __('back_to_home') ?></a>
            </div>

            <audio id="depositSuccessSound" src="assets/sounds/notification.mp3" autoplay></audio>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var snd = document.getElementById('depositSuccessSound');
                    if (snd) {
                        snd.play().catch(function(e) {
                            console.log("Autoplay prevented by browser.");
                        });
                    }
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                });
            </script>
        <?php endif; ?>
    </div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
