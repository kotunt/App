<?php

// 1. Bootstrap the application
require_once __DIR__ . '/bootstrap.php';

// 2. Run the controller logic (procedural, sets up $user, $db, etc.)
require_once __DIR__ . '/src/controllers/HomeController.php';

// 3. Render the view
require_once __DIR__ . '/src/views/home_view.php';
