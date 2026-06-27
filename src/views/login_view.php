<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<body class="max-w-md mx-auto relative min-h-screen bg-gray-100 shadow-xl flex items-center justify-center p-4">

    <div class="bg-white w-full rounded-2xl shadow-lg p-6">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-lock text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-primary"><?= __('login_title') ?></h1>
            <p class="text-gray-500 text-sm mt-1"><?= __('welcome_to_app') ?></p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                <span class="block sm:inline"><?= htmlspecialchars($error_message) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="phone"><?= __('phone_number') ?></label>
                <input class="shadow appearance-none border rounded w-full py-3 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" id="phone" name="phone" type="text" placeholder="<?= __('phone_placeholder') ?>" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password"><?= __('password') ?></label>
                <input class="shadow appearance-none border rounded w-full py-3 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500" id="password" name="password" type="password" placeholder="••••••••" required>
                <div class="text-right mt-2">
                    <a href="forgot_password.php" class="inline-block text-sm text-primary hover:text-blue-800 hover:underline"><?= __('forgot_password') ?></a>
                </div>
            </div>
            <button class="bg-primary hover:bg-blue-800 text-white font-bold py-3 px-4 rounded w-full focus:outline-none focus:shadow-outline transition duration-200 mb-4" type="submit">
                <?= __('login_button') ?>
            </button>
            <div class="text-center">
                <span class="text-gray-600 text-sm"><?= __('no_account_yet') ?></span>
                <a href="register.php" class="text-primary text-sm font-bold ml-1 hover:underline"><?= __('register_new_account') ?></a>
            </div>
        </form>
    </div>

</body>
</html>
