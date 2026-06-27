<?php

use App\Core\Cache;
use App\Core\Database;

if (!function_exists('get_all_settings')) {
    /**
     * Fetches all settings from the cache or database.
     *
     * @return array An associative array of settings.
     */
    function get_all_settings(): array
    {
        $cache = Cache::getInstance();
        $cacheKey = 'app_settings';

        $settings = $cache->get($cacheKey);

        if ($settings === false) {
            $conn = Database::getInstance()->getConnection();
            $stmt = $conn->query("SELECT setting_key, setting_value FROM settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Cache the settings for 1 hour
            $cache->set($cacheKey, $settings, 3600);
        }

        return $settings;
    }
}

// Add other global helper functions here...