<?php
// Accessible announcement partial
// Usage: include_once __DIR__ . '/partials/announcement.php';
?>
<div class="marquee-wrap" role="region" aria-live="polite" aria-atomic="true">
  <div class="animate-marquee text-sm md:text-base font-bold text-gray-700 mt-1 md:mt-0">
    <?= htmlspecialchars(__('welcome_marquee')) ?>
  </div>
</div>
