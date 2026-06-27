<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<body class="min-h-screen flex items-center justify-center p-4 bg-brand-gradient relative overflow-hidden">

    <!-- Decorative background -->
    <div class="absolute top-[-15%] left-[-10%] w-96 h-96 bg-gold-400/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-[-15%] right-[-10%] w-[28rem] h-[28rem] bg-blue-400/20 rounded-full blur-3xl"></div>
    <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 28px 28px;"></div>

    <div class="w-full max-w-md relative z-10 animate-fadeUp">
        <div class="glass rounded-4xl shadow-premium p-7 md:p-9">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gold-gradient text-primary-900 rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-gold rotate-3">
                    <i class="fas fa-user-lock text-3xl"></i>
                </div>
                <h1 class="text-3xl font-extrabold text-primary tracking-tight"><?= __('login_title') ?></h1>
                <p class="text-gray-500 text-sm mt-2"><?= __('welcome_to_app') ?></p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl relative mb-5 text-sm flex items-center gap-2" role="alert">
                    <i class="fas fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error_message) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="space-y-5">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2 ml-1" for="phone"><i class="fas fa-phone text-primary/70 mr-1.5"></i><?= __('phone_number') ?></label>
                    <input class="w-full py-3.5 px-4 rounded-2xl border border-gray-200 bg-white/70 text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition-all" id="phone" name="phone" type="text" placeholder="<?= __('phone_placeholder') ?>" autocomplete="username" required>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2 ml-1" for="password"><i class="fas fa-lock text-primary/70 mr-1.5"></i><?= __('password') ?></label>
                    <div class="relative">
                        <input class="w-full py-3.5 px-4 pr-12 rounded-2xl border border-gray-200 bg-white/70 text-gray-800 leading-tight focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-primary-400 transition-all" id="password" name="password" type="password" placeholder="••••••••" autocomplete="current-password" required>
                        <button type="button" onclick="const p=document.getElementById('password');const i=this.querySelector('i');if(p.type==='password'){p.type='text';i.className='fas fa-eye-slash';}else{p.type='password';i.className='fas fa-eye';}" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="text-right mt-2">
                        <a href="forgot_password.php" class="inline-block text-sm text-primary hover:text-primary-800 hover:underline font-semibold"><?= __('forgot_password') ?></a>
                    </div>
                </div>
                <button class="w-full bg-brand-gradient hover:shadow-premium text-white font-bold py-3.5 px-4 rounded-2xl shadow-card focus:outline-none transition-all duration-300 hover:-translate-y-0.5 flex items-center justify-center gap-2" type="submit">
                    <i class="fas fa-right-to-bracket"></i> <?= __('login_button') ?>
                </button>
                <div class="text-center pt-1">
                    <span class="text-gray-500 text-sm"><?= __('no_account_yet') ?></span>
                    <a href="register.php" class="text-primary text-sm font-bold ml-1 hover:underline"><?= __('register_new_account') ?></a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
