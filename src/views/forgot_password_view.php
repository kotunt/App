<?php 
require_once __DIR__ . '/../../includes/header.php'; 
?>

<style>
    .glass-panel { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); }
</style>

<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4 md:p-8">

    <div class="fixed top-[-10%] left-[-10%] w-96 h-96 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob"></div>
    <div class="fixed bottom-[-10%] right-[-10%] w-96 h-96 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>

    <div class="w-full max-w-md lg:max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden relative z-10 animate__animated animate__fadeInUp flex flex-col lg:flex-row">
        
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-primary to-blue-900 p-12 flex-col justify-center text-white relative overflow-hidden">
            <div class="absolute top-10 right-10 w-32 h-32 bg-white opacity-5 rounded-full blur-2xl"></div>
            <div class="absolute bottom-10 left-10 w-48 h-48 bg-blue-400 opacity-20 rounded-full blur-3xl"></div>
            
            <div class="relative z-10">
                <div class="w-20 h-20 glass-panel rounded-2xl flex items-center justify-center mb-8 border border-white/20 shadow-lg">
                    <i class="fas fa-user-shield text-4xl text-yellow-400"></i>
                </div>
                <h2 class="text-3xl lg:text-4xl font-extrabold mb-4 leading-tight">စကားဝှက် <br><span class="text-yellow-400">မေ့နေပါသလား?</span></h2>
                <p class="text-blue-100 text-base lg:text-lg mb-8 leading-relaxed opacity-90">
                    လုံခြုံစိတ်ချရသော နည်းလမ်းဖြင့် သင့်အကောင့်အား အလွယ်တကူ ပြန်လည်ရယူလိုက်ပါ။
                </p>
                
                <ul class="space-y-4">
                    <li class="flex items-center text-blue-50 text-base">
                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center mr-4">
                            <i class="fas fa-check text-green-400"></i>
                        </div>
                        အချက်အလက် မှန်ကန်မှု စစ်ဆေးခြင်း
                    </li>
                    <li class="flex items-center text-blue-50 text-base">
                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center mr-4">
                            <i class="fab fa-telegram-plane text-blue-300"></i>
                        </div>
                        Telegram မှတစ်ဆင့် OTP ပေးပို့ခြင်း
                    </li>
                    <li class="flex items-center text-blue-50 text-base">
                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center mr-4">
                            <i class="fas fa-key text-yellow-400"></i>
                        </div>
                        စကားဝှက်အသစ် လုံခြုံစွာ ပြောင်းလဲခြင်း
                    </li>
                </ul>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex flex-col justify-center bg-white p-6 md:p-8 lg:p-12 relative">
            
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-blue-50 text-primary border border-blue-100 rounded-full flex items-center justify-center mx-auto mb-4 lg:hidden shadow-sm">
                    <i class="fas fa-key text-2xl"></i>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800"><?= __('forgot_password_title') ?></h1>
                <p class="text-gray-500 text-sm md:text-base mt-2"><?= __('forgot_password_subtitle') ?></p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-r-xl relative mb-6 text-sm md:text-base font-bold flex items-center shadow-sm animate__animated animate__shakeX">
                    <i class="fas fa-exclamation-circle text-lg md:text-xl mr-3"></i> <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message) && $step !== 4): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-r-xl relative mb-6 text-sm md:text-base font-bold flex items-center shadow-sm">
                    <i class="fas fa-check-circle text-lg md:text-xl mr-3"></i> <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <form method="POST" action="forgot_password.php" class="space-y-5">
                    <p class="text-sm md:text-base text-gray-600 mb-2 text-center md:text-left"><?= __('forgot_pw_step1_desc') ?></p>
                    
                    <div>
                        <label class="block text-gray-700 text-sm md:text-base font-bold mb-2 ml-1"><i class="fas fa-phone-alt text-primary mr-1"></i> <?= __('phone_number') ?></label>
                        <input class="w-full px-4 md:px-5 py-3.5 md:py-4 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 focus:bg-white text-sm md:text-base" name="phone" type="text" placeholder="<?= __('phone_placeholder') ?>" required>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm md:text-base font-bold mb-2 ml-1"><i class="fas fa-user text-primary mr-1"></i> <?= __('username') ?></label>
                        <input class="w-full px-4 md:px-5 py-3.5 md:py-4 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 focus:bg-white text-sm md:text-base" name="username" type="text" placeholder="<?= __('username_placeholder_register') ?>" required>
                    </div>
                    
                    <button class="w-full bg-primary hover:bg-blue-800 text-white font-bold py-3.5 md:py-4 rounded-xl text-base md:text-lg shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 mt-2" name="verify" type="submit">
                        <?= __('continue_button') ?> <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                    
                    <div class="text-center mt-6 pt-4 border-t border-gray-100">
                        <p class="text-xs md:text-sm text-red-500 mb-3 bg-red-50 py-2 px-3 rounded-lg inline-block"><i class="fas fa-info-circle"></i> <?= __('forgot_phone_notice') ?></p><br>
                        <a href="login.php" class="inline-flex items-center text-primary text-sm md:text-base font-bold hover:underline transition-colors"><i class="fas fa-arrow-left mr-1.5"></i> <?= __('back_button') ?></a>
                    </div>
                </form>

            <?php elseif ($step === 2): ?>
                <form method="POST" action="forgot_password.php" class="space-y-5 text-center">
                    <div class="w-20 h-20 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-2 border border-blue-100">
                        <i class="fab fa-telegram-plane text-4xl"></i>
                    </div>
                    <p class="text-sm md:text-base text-gray-600 mb-6"><?= __('forgot_pw_step2_desc') ?></p>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm md:text-base font-bold mb-3"><?= __('otp_code') ?></label>
                        <input class="w-full py-4 px-4 border-2 border-gray-300 rounded-xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none text-center tracking-[0.5em] font-mono text-2xl md:text-3xl shadow-inner transition-all bg-gray-50 focus:bg-white" name="otp" type="text" placeholder="------" maxlength="6" required autocomplete="off">
                    </div>
                    
                    <button class="w-full bg-primary hover:bg-blue-800 text-white font-bold py-3.5 md:py-4 rounded-xl text-base md:text-lg shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300" name="verify_otp" type="submit">
                        <i class="fas fa-check-circle mr-1"></i> <?= __('confirm_button') ?>
                    </button>
                    
                    <div class="text-center mt-6">
                        <a href="forgot_password.php" class="inline-block text-gray-500 hover:text-primary text-sm md:text-base font-bold hover:underline transition-colors"><i class="fas fa-redo-alt mr-1"></i> <?= __('start_over_button') ?></a>
                    </div>
                </form>

            <?php elseif ($step === 3): ?>
                <form method="POST" action="forgot_password.php" class="space-y-5">
                    <div>
                        <label class="block text-gray-700 text-sm md:text-base font-bold mb-2 ml-1"><i class="fas fa-lock text-primary mr-1"></i> <?= __('new_password') ?></label>
                        <input class="w-full px-4 md:px-5 py-3.5 md:py-4 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 focus:bg-white text-sm md:text-base" name="new_password" type="password" placeholder="<?= __('password_placeholder') ?>" minlength="6" required>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 text-sm md:text-base font-bold mb-2 ml-1"><i class="fas fa-lock text-primary mr-1"></i> <?= __('confirm_new_password') ?></label>
                        <input class="w-full px-4 md:px-5 py-3.5 md:py-4 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all bg-gray-50 focus:bg-white text-sm md:text-base" name="confirm_password" type="password" placeholder="<?= __('password_placeholder') ?>" minlength="6" required>
                    </div>
                    
                    <button class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 md:py-4 rounded-xl text-base md:text-lg shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 mt-4" name="reset" type="submit">
                        <i class="fas fa-save mr-1"></i> <?= __('confirm_button') ?>
                    </button>
                </form>

            <?php elseif ($step === 4): ?>
                <div class="text-center py-6">
                    <div class="text-green-500 text-6xl md:text-7xl mb-6 animate-bounce"><i class="fas fa-check-circle"></i></div>
                    <h3 class="text-xl md:text-2xl font-bold text-gray-800 mb-3">အောင်မြင်ပါသည်</h3>
                    <p class="text-sm md:text-base text-gray-600 mb-8 leading-relaxed font-medium"><?= htmlspecialchars($success_message) ?></p>
                    <a href="login.php" class="w-full inline-block bg-primary hover:bg-blue-800 text-white font-bold py-3.5 md:py-4 rounded-xl text-base md:text-lg shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                        <?= __('login_again_button') ?> <i class="fas fa-sign-in-alt ml-1"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
