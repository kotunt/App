<?php
// core/config.php - Read DB credentials from environment variables.
// For local development you can use a .env file and phpdotenv to load variables.
// DO NOT commit real credentials. Use .env.example as a template.

// Read from environment with sensible defaults for local dev
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'thai_2d3d_db';

define('DB_HOST', $db_host);
define('DB_USER', $db_user);
define('DB_PASS', $db_pass);
define('DB_NAME', $db_name);

// Application environment: production, staging, development
define('APP_ENV', getenv('APP_ENV') ?: 'production');

// Optional: toggle display_errors based on APP_ENV elsewhere (core/db_connect.php currently sets display_errors = 0)
