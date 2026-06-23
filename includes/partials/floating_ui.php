<?php
// Floating UI partial: theme toggle, language links, notification opt-in
$base_url = '';
if (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/agent/') !== false) {
    $base_url = '../';
}
?>
<div id="floatingUI" class="fixed top-24 right-4 md:top-8 md:right-8 lg:right-10 z-50 flex flex-col md:flex-row gap-1 md:gap-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur shadow-lg p-1 rounded-lg">
  <button id="floatingThemeToggle" aria-label="Toggle Theme" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full md:rounded-xl text-sm md:text-base transition-all">
    <i class="fas fa-moon dark:hidden"></i>
    <i class="fas fa-sun hidden dark:inline-block text-yellow-400"></i>
  </button>

  <div class="w-6 h-px md:w-px md:h-6 bg-gray-300 dark:bg-gray-600"></div>

  <a href="?lang=mm" title="မြန်မာ" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full md:rounded-xl text-[10px] md:text-xs font-bold transition-all">MM</a>
  <a href="?lang=en" title="English" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full md:rounded-xl text-[10px] md:text-xs font-bold transition-all">EN</a>

  <div class="w-6 h-px md:w-px md:h-6 bg-gray-300 dark:bg-gray-600"></div>

  <button id="floatingNotifyBtn" aria-label="Enable Notifications" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full md:rounded-xl text-sm md:text-base bg-yellow-400 hover:bg-yellow-300">
    <i class="fas fa-bell"></i>
  </button>
</div>

<script>
(function(){
  // Initialize handlers for floating UI
  const root = document.getElementById('floatingUI');
  if (!root) return;

  const themeBtn = document.getElementById('floatingThemeToggle');
  if (themeBtn) {
    themeBtn.addEventListener('click', () => {
      document.documentElement.classList.toggle('dark');
      const isDark = document.documentElement.classList.contains('dark');
      localStorage.setItem('user_theme', isDark ? 'dark' : 'light');
      if (navigator.vibrate) navigator.vibrate(40);
    });
  }

  const notifyBtn = document.getElementById('floatingNotifyBtn');
  if (notifyBtn) {
    notifyBtn.addEventListener('click', () => {
      if (typeof requestNotificationPermission === 'function') {
        requestNotificationPermission();
        notifyBtn.classList.add('opacity-60');
      }
    });
  }
})();
</script>
