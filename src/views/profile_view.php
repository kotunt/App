<?php 
// This is the view file for the profile page.
// The controller passes data to this view, which is then rendered.
require_once __DIR__ . '/../../includes/header.php'; 
?>

<body class="w-full md:max-w-4xl lg:max-w-5xl mx-auto min-h-screen bg-gray-50 md:shadow-2xl md:border-x border-gray-200 transition-all duration-300 pb-20 md:pb-24">

    <div class="bg-brand-gradient text-white flex items-center p-4 md:p-6 sticky top-0 z-20 shadow-card">
        <a href="index.php" class="mr-4 text-xl md:text-2xl w-6 md:w-10 hover:scale-110 transition-transform"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl md:text-2xl font-bold flex-1 text-center pr-6 md:pr-10 tracking-wide"><?= __('edit_profile') ?></h1>
    </div>

    <div class="p-4 md:p-8">
        <?php if (!empty($success_message)): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 md:py-4 rounded-2xl relative mb-5 text-sm md:text-base font-medium shadow-soft"><?= $success_message ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 md:py-4 rounded-2xl relative mb-5 text-sm md:text-base font-medium shadow-soft"><?= $error_message ?></div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-6">
            
            <div class="md:col-span-7 lg:col-span-8 flex flex-col gap-4 md:gap-6">
                
                <div class="bg-gold-gradient rounded-3xl p-5 md:p-6 shadow-gold text-primary-900 transform hover:-translate-y-0.5 transition-transform duration-300 relative overflow-hidden">
                    <div class="absolute top-[-20%] right-[-5%] w-40 h-40 bg-white/20 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="flex justify-between items-center mb-4 relative">
                        <div class="font-bold text-lg md:text-xl flex items-center"><i class="fas fa-crown mr-2.5 text-2xl drop-shadow-md"></i><?= htmlspecialchars($current_level) ?> <?= __('level') ?></div>
                        <div class="text-xs md:text-sm bg-primary-900/15 px-3 py-1.5 rounded-xl font-semibold backdrop-blur-sm"><?= __('total_bets') ?> <span class="font-bold ml-1"><?= number_format($lifetime_bet) ?></span></div>
                    </div>
                    
                    <?php if ($next_level !== 'Max'): ?>
                    <div class="text-sm md:text-base font-bold mb-2 relative">
                        <?= __('next_level') ?> <?= $next_level ?> <span class="text-primary-900/70 font-normal text-xs md:text-sm ml-1">(<?= number_format($next_threshold) ?> <?= __('currency') ?>)</span>
                    </div>
                    <div class="w-full bg-primary-900/15 rounded-full h-2.5 md:h-3 mb-2 shadow-inner overflow-hidden relative">
                        <div class="bg-white h-2.5 md:h-3 rounded-full shadow-md relative" style="width: <?= $progress_percent ?>%">
                            <div class="absolute top-0 right-0 bottom-0 w-4 bg-white/50 blur-[2px]"></div>
                        </div>
                    </div>
                    <div class="text-[10px] md:text-xs text-right text-primary-900/70 font-medium relative">
                        <?= number_format($next_threshold - $lifetime_bet) ?> <?= __('needed_for_next_level') ?>
                    </div>
                    <?php else: ?>
                    <div class="text-sm md:text-base font-bold text-center mt-3 bg-white/30 py-2 rounded-xl backdrop-blur-sm relative">
                        <i class="fas fa-star text-primary-800 mr-1"></i> <?= __('max_vip_reached') ?> <i class="fas fa-star text-primary-800 ml-1"></i>
                    </div>
                    <?php endif; ?>
                </div>

                <form method="POST" action="" class="bg-white p-6 md:p-8 rounded-3xl shadow-card border border-gray-100">
                    
                    <div class="text-center mb-8">
                        <div class="relative w-24 h-24 md:w-32 md:h-32 mx-auto mb-3">
                            <?php if (!empty($user_data['avatar'])): ?>
                                <img src="<?= htmlspecialchars($user_data['avatar']) ?>" alt="Avatar" class="w-full h-full rounded-full object-cover shadow-premium border-4 border-white">
                            <?php else: ?>
                                <div class="w-full h-full bg-brand-gradient text-white rounded-full flex items-center justify-center shadow-premium border-4 border-white">
                                    <i class="fas fa-user text-4xl md:text-5xl"></i>
                                </div>
                            <?php endif; ?>
                            
                            <a href="update_avatar.php" class="absolute bottom-0 right-0 md:bottom-1 md:right-1 bg-gold-gradient text-primary-900 w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center cursor-pointer shadow-gold hover:scale-110 transition-all border-2 border-white">
                                <i class="fas fa-camera text-sm md:text-base"></i>
                            </a>
                        </div>
                        <p class="text-xs md:text-sm text-gray-500 font-bold"><?= __('change_profile_picture') ?></p>
                    </div>

                    <div class="mb-5 md:mb-6">
                        <label class="block text-gray-700 text-sm md:text-base font-bold mb-2 ml-1"><i class="fas fa-phone text-primary/70 mr-1.5"></i><?= __('phone_number') ?></label>
                        <input type="text" name="phone_number" value="<?= htmlspecialchars($user_data['phone_number']) ?>" class="w-full py-3.5 px-4 rounded-2xl border border-gray-200 bg-white/70 text-gray-800 font-bold leading-tight focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition-all text-sm md:text-base" required>
                    </div>
                    
                    <div class="mb-5 md:mb-6">
                        <label class="block text-gray-700 text-sm md:text-base font-bold mb-2 ml-1"><i class="fas fa-user text-primary/70 mr-1.5"></i><?= __('username') ?></label>
                        <input type="text" name="username" value="<?= htmlspecialchars($user_data['username']) ?>" class="w-full py-3.5 px-4 rounded-2xl border border-gray-200 bg-white/70 text-gray-800 font-bold leading-tight focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition-all text-sm md:text-base" required>
                    </div>

                    <div class="mb-6 md:mb-8">
                        <label class="block text-gray-700 text-sm md:text-base font-bold mb-2 ml-1"><i class="fab fa-telegram text-blue-500 mr-1.5"></i> <?= __('telegram_chat_id_optional') ?></label>
                        <input type="text" name="telegram_chat_id" value="<?= htmlspecialchars($user_data['telegram_chat_id'] ?? '') ?>" placeholder="<?= __('telegram_chat_id_placeholder') ?>" class="w-full py-3.5 px-4 rounded-2xl border border-gray-200 bg-white/70 text-gray-800 font-mono leading-tight focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition-all text-sm md:text-base">
                        <p class="text-[10px] md:text-xs text-gray-500 mt-1.5 font-medium"><?= __('telegram_chat_id_help') ?></p>
                    </div>

                    <button type="submit" class="w-full bg-brand-gradient hover:shadow-premium text-white font-bold py-3.5 md:py-4 rounded-2xl text-lg md:text-xl shadow-card hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> <?= __('save') ?>
                    </button>
                </form>

                <div class="bg-white p-5 md:p-6 rounded-3xl shadow-card border border-gray-100 border-l-4 <?= $lifetime_profit >= 0 ? 'border-green-500' : 'border-red-500' ?>">
                    <h3 class="font-bold text-gray-700 mb-3 md:mb-4 border-b pb-3 text-sm md:text-base flex items-center">
                        <i class="fas fa-chart-line mr-2 <?= $lifetime_profit >= 0 ? 'text-green-500' : 'text-red-500' ?> text-lg"></i> 
                        <?= __('lifetime_summary') ?>
                    </h3>
                    <div class="space-y-2 md:space-y-3 bg-gray-50/50 p-3 md:p-4 rounded-2xl">
                        <div class="flex justify-between items-center">
                            <span class="text-sm md:text-base text-gray-500 font-medium"><?= __('total_bet_amount') ?></span>
                            <span class="text-sm md:text-base font-bold text-red-500">- <?= number_format($lifetime_bet) ?> <?= __('currency') ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm md:text-base text-gray-500 font-medium"><?= __('total_win_amount') ?></span>
                            <span class="text-sm md:text-base font-bold text-green-600">+ <?= number_format($lifetime_win) ?> <?= __('currency') ?></span>
                        </div>
                        <div class="flex justify-between items-center border-t border-gray-200 pt-3 md:pt-4 mt-1 md:mt-2">
                            <span class="text-sm md:text-base font-bold text-gray-700 uppercase tracking-wide"><?= __('net_profit_loss') ?></span>
                            <span class="text-base md:text-xl font-bold <?= $lifetime_profit >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                                <?= $lifetime_profit > 0 ? '+' : '' ?><?= number_format($lifetime_profit) ?> <?= __('currency') ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-5 text-center border-t border-gray-100 pt-4 md:pt-5">
                        <a href="export_history.php" class="inline-block bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 hover:border-gray-300 text-xs md:text-sm font-bold py-2 md:py-2.5 px-5 md:px-6 rounded-xl shadow-sm transition-all duration-300 hover:-translate-y-0.5">
                            <i class="fas fa-file-excel mr-1.5 text-green-600"></i> <?= __('download_all_history') ?>
                        </a>
                    </div>
                </div>

            </div>

            <div class="md:col-span-5 lg:col-span-4 flex flex-col gap-3 md:gap-4 mt-2 md:mt-0">
                
                <h3 class="font-bold text-gray-800 text-sm md:text-base px-2 hidden md:block border-b border-gray-200 pb-2 mb-2"><?= __('account_settings') ?? 'Account Settings' ?></h3>

                <a href="payment_accounts.php" class="group bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center hover:bg-blue-50/50 hover:border-blue-100 hover:shadow-md transition-all duration-300">
                    <div>
                        <p class="font-bold text-gray-800 md:text-lg group-hover:text-primary transition-colors"><i class="fas fa-wallet text-primary mr-2 md:mr-3"></i> <?= __('withdrawal_accounts') ?></p>
                        <p class="text-xs md:text-sm text-gray-500 mt-1 md:mt-1.5 ml-6 md:ml-8"><?= __('withdrawal_accounts_desc') ?></p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-primary group-hover:translate-x-1 transition-all"></i>
                </a>

                <a href="referral.php" class="group bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center hover:bg-blue-50/50 hover:border-blue-100 hover:shadow-md transition-all duration-300">
                    <div>
                        <p class="font-bold text-gray-800 md:text-lg group-hover:text-primary transition-colors"><i class="fas fa-share-alt text-primary mr-2 md:mr-3"></i> <?= __('referral_code') ?></p>
                        <p class="text-xs md:text-sm text-gray-500 mt-1 md:mt-1.5 ml-6 md:ml-8"><?= __('referral_code_desc') ?></p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-primary group-hover:translate-x-1 transition-all"></i>
                </a>

                <a href="setup_pin.php" class="group bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center hover:bg-blue-50/50 hover:border-blue-100 hover:shadow-md transition-all duration-300">
                    <div>
                        <p class="font-bold text-gray-800 md:text-lg group-hover:text-primary transition-colors"><i class="fas fa-shield-alt text-primary mr-2 md:mr-3"></i> <?= __('setup_security_pin') ?></p>
                        <p class="text-xs md:text-sm text-gray-500 mt-1 md:mt-1.5 ml-6 md:ml-8"><?= __('setup_security_pin_desc') ?></p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-primary group-hover:translate-x-1 transition-all"></i>
                </a>

                <a href="change_password.php" class="group bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center hover:bg-blue-50/50 hover:border-blue-100 hover:shadow-md transition-all duration-300">
                    <div>
                        <p class="font-bold text-gray-800 md:text-lg group-hover:text-primary transition-colors"><i class="fas fa-lock text-primary mr-2 md:mr-3"></i> <?= __('change_password') ?></p>
                        <p class="text-xs md:text-sm text-gray-500 mt-1 md:mt-1.5 ml-6 md:ml-8"><?= __('change_password_desc') ?></p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-primary group-hover:translate-x-1 transition-all"></i>
                </a>

                <a href="setup_2fa.php" class="group bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center hover:bg-green-50/50 hover:border-green-100 hover:shadow-md transition-all duration-300">
                    <div>
                        <p class="font-bold text-gray-800 md:text-lg group-hover:text-green-600 transition-colors"><i class="fas fa-shield-check text-green-500 mr-2 md:mr-3"></i> <?= __('2fa_security') ?></p>
                        <p class="text-xs md:text-sm text-gray-500 mt-1 md:mt-1.5 ml-6 md:ml-8"><?= __('2fa_security_desc') ?></p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-green-500 group-hover:translate-x-1 transition-all"></i>
                </a>

                <a href="transaction_history.php" class="group bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center hover:bg-blue-50/50 hover:border-blue-100 hover:shadow-md transition-all duration-300">
                    <div>
                        <p class="font-bold text-gray-800 md:text-lg group-hover:text-primary transition-colors"><i class="fas fa-exchange-alt text-primary mr-2 md:mr-3"></i> <?= __('tx_history') ?></p>
                        <p class="text-xs md:text-sm text-gray-500 mt-1 md:mt-1.5 ml-6 md:ml-8"><?= __('tx_history_desc') ?></p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-primary group-hover:translate-x-1 transition-all"></i>
                </a>

                <a href="support.php" class="group bg-white p-4 md:p-5 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center hover:bg-blue-50/50 hover:border-blue-100 hover:shadow-md transition-all duration-300 mb-2 md:mb-0">
                    <div>
                        <p class="font-bold text-gray-800 md:text-lg group-hover:text-primary transition-colors"><i class="fas fa-headset text-primary mr-2 md:mr-3"></i> <?= __('support_contact') ?></p>
                        <p class="text-xs md:text-sm text-gray-500 mt-1 md:mt-1.5 ml-6 md:ml-8"><?= __('support_contact_desc') ?></p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-primary group-hover:translate-x-1 transition-all"></i>
                </a>

            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>