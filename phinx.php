<?php

// Load application config to get DB constants
require_once __DIR__ . '/core/config.php';

$db_driver = DB_DRIVER;
$db_host = DB_HOST;
$db_user = DB_USER;
$db_pass = DB_PASS;
$db_name = DB_NAME;
$db_path = DB_PATH;

$default_environment = 'development';

$environments = [
    'default_migration_table' => 'phinxlog',
    'default_database' => $default_environment,
    'production' => [
        'adapter' => 'mysql',
        'host' => $db_host,
        'name' => $db_name,
        'user' => $db_user,
        'pass' => $db_pass,
        'port' => '3306',
        'charset' => 'utf8mb4',
    ],
    'development' => [
        'adapter' => $db_driver,
        'host' => $db_host,
        'name' => ($db_driver === 'sqlite') ? $db_path : $db_name,
        'user' => $db_user,
        'pass' => $db_pass,
        'port' => '3306',
        'charset' => 'utf8mb4',
    ],
    'testing' => [
        'adapter' => 'sqlite',
        'name' => ':memory:', // Use in-memory database for tests
        'charset' => 'utf8',
    ]
];

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/database/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/database/seeds'
    ],
    'environments' => $environments,
    'version_order' => 'creation'
];