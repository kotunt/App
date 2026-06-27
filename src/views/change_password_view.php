<?php 
require_once __DIR__ . '/../../includes/header.php'; 
?>
<body class="w-full md:max-w-3xl lg:max-w-5xl mx-auto min-h-screen bg-gray-50 md:shadow-2xl md:border-x border-gray-200 transition-all duration-300 pb-20 md:pb-24 flex flex-col">

    <div class="bg-brand-gradient text-white flex items-center p-4 md:p-6 sticky top-0 z-20 shadow-card w-full">
        <a href="profile.php" class="mr-4 text-xl md:text-2xl w-6 md:w-10 hover:scale-110 transition-transform"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl md:text-2xl font-bold flex-1 text-center pr-6 md:pr-10 tracking-wide"><?= __('change_password') ?></h1>
    </div>

    <div class="p-4 md:p-8 flex-1 flex flex-col items-center justify-center md:mt-8">
        
        <div class="w-full max-w-md animate-fadeUp">
            
            <?php if (!empty($success_message)): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl relative mb-5 text-sm md:text-base text-center font-bold shadow-soft">
                    <i class="fas fa-check-circle text-green-500 text-2xl mb-2 block"></i>
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 md:py-4 rounded-2xl relative mb-5 text-sm md:text-base text-center font-medium shadow-soft flex items-center justify-center gap-2">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="change_password.php" class="bg-white p-6 md:p-10 rounded-3xl md:rounded-4xl shadow-card border border-gray-100">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div class="text-center mb-7 md:mb-9">
                    <div class="w-20 h-20 md:w-24 md:h-24 bg-gold-gradient text-primary-900 rounded-3xl flex items-center justify-center mx-auto mb-4 md:mb-5 shadow-gold rotate-3">
                        <i class="fas fa-lock text-3xl md:text-4xl"></i>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-primary tracking-tight"><?= __('change_password') ?></h2>
                </div>

                <div class="mb-5 md:mb-6">
                    <label class="block text-gray-700 text-sm md:text-base font-bold mb-2 ml-1"><i class="fas fa-key text-primary/70 mr-1.5"></i><?= __('new_password') ?></label>
                    <input type="password" name="new_password" placeholder="<?= __('min_6_chars') ?>" minlength="6" class="w-full py-3.5 px-4 rounded-2xl border border-gray-200 bg-white/70 text-gray-800 tracking-wider leading-tight focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition-all text-sm md:text-base" required>
                </div>
                
                <div class="mb-6 md:mb-8">
                    <label class="block text-gray-700 text-sm md:text-base font-bold mb-2 ml-1"><i class="fas fa-check-double text-primary/70 mr-1.5"></i><?= __('confirm_new_password') ?></label>
                    <input type="password" name="confirm_password" placeholder="<?= __('reenter_new_password') ?>" minlength="6" class="w-full py-3.5 px-4 rounded-2xl border border-gray-200 bg-white/70 text-gray-800 tracking-wider leading-tight focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition-all text-sm md:text-base" required>
                </div>
                
                <button type="submit" class="w-full bg-brand-gradient hover:shadow-premium text-white font-bold py-3.5 md:py-4 rounded-2xl text-lg md:text-xl shadow-card hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2">
                    <i class="fas fa-check-circle"></i> <?= __('confirm') ?>
                </button>

            </form>
            
            <div class="mt-6 md:mt-8 text-center border-t border-gray-100 pt-5">
                <a href="profile.php" class="text-gray-500 hover:text-primary text-sm md:text-base font-medium flex items-center justify-center transition-colors">
                    <i class="fas fa-arrow-left mr-1.5"></i> <?= __('back') ?>
                </a>
            </div>

        </div>
    </div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
