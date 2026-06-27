<?php
$base_url = '';
if (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false || strpos($_SERVER['SCRIPT_NAME'], '/agent/') !== false) {
    $base_url = '../';
}

if (file_exists(dirname(__DIR__) . '/lang/language.php')) {
    require_once dirname(__DIR__) . '/lang/language.php';
}

if (!function_exists('safe_url')) {
    /**
     * Allow only safe URL schemes for href/src to prevent javascript:/data: XSS.
     * Returns '#' for anything that is not an allowed absolute URL or a relative path.
     */
    function safe_url($url, array $extra_schemes = []) {
        $url = trim((string)$url);
        if ($url === '') return '#';
        $allowed = array_merge(['http', 'https'], $extra_schemes);
        if (preg_match('#^([a-zA-Z][a-zA-Z0-9+.\-]*):#', $url, $m)) {
            return in_array(strtolower($m[1]), $allowed, true) ? $url : '#';
        }
        // No scheme: treat as relative path/anchor, reject protocol-relative & control chars.
        if (strpos($url, '//') === 0) return '#';
        if (preg_match('/[\x00-\x1F\x7F]/', $url)) return '#';
        return $url;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'mm') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a428a">
    <link rel="manifest" href="manifest.json">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Thai 2D3D' ?></title>
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['admin_csrf_token'] ?? '') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <?php 
       // db_connect မပါသေးပါက ထည့်ပေးရန်လိုအပ်ပါသည် (index.php တွင် ပါပြီးသားဖြစ်၍ ပြဿနာမရှိ)
       require_once __DIR__ . '/seo.php'; 
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Padauk:wght@400;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#1a428a',
                            50:  '#eef3fb',
                            100: '#d9e3f5',
                            200: '#b3c7eb',
                            300: '#8aa8de',
                            400: '#5a82cc',
                            500: '#2f5cb0',
                            600: '#224a96',
                            700: '#1a428a',
                            800: '#15336a',
                            900: '#0f2750',
                            950: '#0a1a38',
                        },
                        gold: {
                            DEFAULT: '#ffd700',
                            300: '#ffe14d',
                            400: '#ffd700',
                            500: '#f5c518',
                            600: '#d4a017',
                        },
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Padauk', 'system-ui', 'sans-serif'],
                        display: ['"Plus Jakarta Sans"', 'Padauk', 'sans-serif'],
                    },
                    boxShadow: {
                        soft: '0 4px 20px -2px rgba(26,66,138,0.08)',
                        card: '0 10px 40px -12px rgba(26,66,138,0.18)',
                        premium: '0 20px 60px -15px rgba(26,66,138,0.30)',
                        gold: '0 8px 30px -6px rgba(255,215,0,0.45)',
                    },
                    borderRadius: {
                        '4xl': '2rem',
                        '5xl': '2.5rem',
                    },
                    keyframes: {
                        shimmer: { '100%': { transform: 'translateX(100%)' } },
                        floaty: { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-6px)' } },
                        fadeUp: { '0%': { opacity: 0, transform: 'translateY(14px)' }, '100%': { opacity: 1, transform: 'translateY(0)' } },
                        glow: { '0%,100%': { opacity: 0.6 }, '50%': { opacity: 1 } },
                    },
                    animation: {
                        shimmer: 'shimmer 2s infinite',
                        floaty: 'floaty 4s ease-in-out infinite',
                        fadeUp: 'fadeUp 0.5s ease-out both',
                        glow: 'glow 2.5s ease-in-out infinite',
                    },
                },
            },
        }
    </script>
  <link rel="apple-touch-icon" sizes="180x180" href="https://file.thai2d3dgame.com/files/notificationImages/all/allNotiNew.png">
  <link rel="icon" type="image/png" href="https://file.thai2d3dgame.com/files/notificationImages/all/allNotiNew.png">


    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Padauk', sans-serif; background-color: #f1f5fb; transition: background-color 0.3s ease; -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility; }
        .bg-primary { background-color: #1a428a; }
        .text-primary { color: #1a428a; }
        .text-gold { color: #ffd700; }
        .bottom-nav-icon { font-size: 1.25rem; color: #6b7280; }
        .bottom-nav-icon.active { color: #1a428a; }

        /* Myanmar text keeps Padauk for proper rendering */
        :lang(my), .font-mm { font-family: 'Padauk', 'Plus Jakarta Sans', sans-serif; }

        /* Premium brand gradients */
        .bg-brand-gradient { background-image: linear-gradient(135deg, #1a428a 0%, #2f5cb0 55%, #15336a 100%); }
        .bg-gold-gradient { background-image: linear-gradient(135deg, #ffe14d 0%, #ffd700 50%, #d4a017 100%); }
        .text-gold-gradient { background-image: linear-gradient(135deg, #ffe98a, #ffd700, #d4a017); -webkit-background-clip: text; background-clip: text; color: transparent; }

        /* Glassmorphism */
        .glass { background: rgba(255,255,255,0.7); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,0.4); }
        html.dark .glass { background: rgba(31,41,55,0.6); border-color: rgba(255,255,255,0.08); }

        /* Card hover polish */
        .card-hover { transition: transform .3s cubic-bezier(.4,0,.2,1), box-shadow .3s cubic-bezier(.4,0,.2,1); }
        .card-hover:hover { transform: translateY(-4px); }

        /* Premium scrollbar */
        ::-webkit-scrollbar { width: 9px; height: 9px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(26,66,138,0.30); border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(26,66,138,0.55); }
        html.dark ::-webkit-scrollbar-thumb { background: rgba(148,163,184,0.35); }

        /* Shimmer overlay helper */
        .shimmer { position: relative; overflow: hidden; }
        .shimmer::after { content: ''; position: absolute; inset: 0; transform: translateX(-100%); background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent); animation: shimmer 2.5s infinite; }

        /* Dark Mode Global Overrides */
        html.dark body { background-color: #0b1220 !important; color: #f9fafb !important; }
        html.dark .bg-white { background-color: #161f33 !important; border-color: #243049 !important; }
        html.dark .bg-gray-100 { background-color: #0b1220 !important; }
        html.dark .bg-gray-50 { background-color: #1c2740 !important; color: #f9fafb !important; }
        html.dark .text-gray-500, html.dark .text-gray-600, html.dark .text-gray-700, html.dark .text-gray-800 { color: #cbd5e1 !important; }
        html.dark .border-gray-100, html.dark .border-gray-200, html.dark .border-gray-300 { border-color: #243049 !important; }
        html.dark .text-primary { color: #7da6f0 !important; }

        /* Nav & Cards Overrides */
        html.dark #bottomNavBar { background-color: #161f33 !important; border-top-color: #243049 !important; }
        html.dark .bottom-nav-icon.active { color: #60a5fa !important; }
        html.dark input, html.dark textarea, html.dark select { 
            background-color: #1c2740 !important; 
            color: #f9fafb !important; 
            border-color: #344056 !important; 
        }
        html.dark .bg-blue-50 { background-color: rgba(59, 130, 246, 0.15) !important; color: #93c5fd !important; }

        /* Focus styles for accessibility */
        :focus-visible {
            outline: 3px solid rgba(47,92,176,0.6);
            outline-offset: 2px;
        }

        /* Marquee-like accessible animation for announcements */
        .marquee-wrap { overflow: hidden; }
        .animate-marquee { display: inline-block; white-space: nowrap; animation: marquee 12s linear infinite; }
        @keyframes marquee { from { transform: translateX(100%); } to { transform: translateX(-100%); } }

        /* Nav & floating UI tweaks */
        .floating-btn { width: 8rem; }
    </style>
    <script>
        // Notification permission helper (do NOT auto-request; call from user gesture)
        function requestNotificationPermission() {
            if ('Notification' in window) {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        console.log('Notification permission granted.');
                    }
                });
            }
        }
        
        // Theme Initialization (Run immediately to prevent flash of white)
        (function() {
            const savedTheme = localStorage.getItem('user_theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js')
                .then(registration => {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                })
                .catch(error => {
                    console.log('ServiceWorker registration failed: ', error);
                });
            });
        }

        // Floating Language & Theme Switcher (Responsive Updated)
        window.addEventListener('DOMContentLoaded', () => {
            const floatingUI = document.createElement('div');
            // Added flex layout to hold both theme and language toggles
            floatingUI.className = 'fixed top-24 right-4 md:top-8 md:right-8 lg:right-10 z-50 flex flex-col md:flex-row gap-1 md:gap-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur shadow-lg p-1 rounded-lg';
            
            floatingUI.innerHTML = `
                <button id="floatingThemeToggle" title="Toggle Theme" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full md:rounded-xl text-sm md:text-base transition-all">
                    <i class="fas fa-moon dark:hidden"></i>
                    <i class="fas fa-sun hidden dark:inline-block text-yellow-400"></i>
                </button>
                
                <div class="w-6 h-px md:w-px md:h-6 bg-gray-300 dark:bg-gray-600"></div>

                <a href="?lang=mm" title="မြန်မာ" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full md:rounded-xl text-[10px] md:text-xs font-bold transition-all">MM</a>
                <a href="?lang=en" title="English" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full md:rounded-xl text-[10px] md:text-xs font-bold transition-all">EN</a>

                <div class="w-6 h-px md:w-px md:h-6 bg-gray-300 dark:bg-gray-600"></div>

                <button id="floatingNotifyBtn" title="Enable Notifications" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full md:rounded-xl text-sm md:text-base bg-yellow-400 hover:bg-yellow-300">
                    <i class="fas fa-bell"></i>
                </button>
            `;
            document.body.appendChild(floatingUI);

            // Theme Toggle Click Listener
            const themeBtn = document.getElementById('floatingThemeToggle');
            themeBtn.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                const isDark = document.documentElement.classList.contains('dark');
                localStorage.setItem('user_theme', isDark ? 'dark' : 'light');
                if (navigator.vibrate) navigator.vibrate(40);
            });

            // Notification Opt-in Button
            const notifyBtn = document.getElementById('floatingNotifyBtn');
            notifyBtn.addEventListener('click', () => {
                requestNotificationPermission();
                notifyBtn.classList.add('opacity-60');
            });
        });
    </script>
</head>
