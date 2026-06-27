<?php

// 1. Bootstrap the application
require_once __DIR__ . '/bootstrap.php';

// 2. Run the controller logic (procedural, sets up $user, $db, etc.)
require_once __DIR__ . '/src/controllers/HomeController.php';

// 3. Render the page head (<!DOCTYPE> ... </head> + Tailwind/styles)
require_once __DIR__ . '/includes/header.php';

// 4. Render the view (<body> ... main content)
require_once __DIR__ . '/src/views/home_view.php';

// 5. Render the footer (bottom navigation + </body></html>)
require_once __DIR__ . '/includes/footer.php';
