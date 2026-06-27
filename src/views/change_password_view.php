<?php 
require_once __DIR__ . '/../../includes/header.php'; 
?>
<body class="w-full md:max-w-3xl lg:max-w-5xl mx-auto min-h-screen bg-gray-50 md:shadow-2xl md:border-x border-gray-200 transition-all duration-300 pb-20 md:pb-24 flex flex-col">

    <div class="bg-primary text-white flex items-center p-4 md:p-6 sticky top-0 z-20 shadow-md w-full">
        <a href="profile.php" class="mr-4 text-xl md:text-2xl w-6 md:w-10 hover:scale-110 transition-transform"><i class="fas fa-arrow-left"></i></a>
        <h1 class="text-xl md:text-2xl font-bold flex-1 text-center pr-6 md:pr-10 tracking-wide"><?= __('change_password') ?></h1>
    </div>

    <div class="p-4 md:p-8 flex-1 flex flex-col items-center justify-center md:mt-8">
        
        <div class="w-full max-w-md">
            
            <?php if (!empty($success_message)): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-xl relative mb-5 text-sm md:text-base text-center font-bold shadow-sm">
                    <i class="fas fa-check-circle text-green-500 text-2xl mb-2 block"></i>
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 md:py-4 rounded-xl relative mb-5 text-sm md:text-base text-center font-medium shadow-sm">
                    <i class="fas fa-exclamation-circle mr-1.5"></i> <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="change_password.php" class="bg-white p-6 md:p-10 rounded-2xl md:rounded-3xl shadow-lg border border-gray-100">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div class="text-center mb-6 md:mb-8">
                    <div class="w-16 h-16 md:w-20 md:h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 md:mb-5 shadow-sm border border-blue-100">
                        <i class="fas fa-lock text-3xl md:text-4xl"></i>
                    </div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 tracking-wide"><?= __('change_password') ?></h2>
                </div>

                <div class="mb-5 md:mb-6">
                    <label class="block text-gray-700 text-sm md:text-base font-bold mb-2 md:mb-3"><?= __('new_password') ?></label>
                    <input type="password" name="new_password" placeholder="<?= __('min_6_chars') ?>" minlength="6" class="w-full py-3 md:py-4 px-4 border border-gray-300 rounded-xl focus:border-blue-500 focus:ring focus:ring-blue-100 focus:outline-none text-gray-700 tracking-wider shadow-sm transition-all text-sm md:text-base" required>
                </div>
                
                <div class="mb-6 md:mb-8">
                    <label class="block text-gray-700 text-sm md:text-base font-bold mb-2 md:mb-3"><?= __('confirm_new_password') ?></label>
                    <input type="password" name="confirm_password" placeholder="<?= __('reenter_new_password') ?>" minlength="6" class="w-full py-3 md:py-4 px-4 border border-gray-300 rounded-xl focus:border-blue-500 focus:ring focus:ring-blue-100 focus:outline-none text-gray-700 tracking-wider shadow-sm transition-all text-sm md:text-base" required>
                </div>
                
                <button type="submit" class="w-full bg-primary hover:bg-blue-800 text-white font-bold py-3.5 md:py-4 rounded-xl text-lg md:text-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                    <i class="fas fa-check-circle mr-1.5"></i> <?= __('confirm') ?>
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
