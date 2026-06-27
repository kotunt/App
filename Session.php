<?php

namespace App\Core;

class Session
{
    /**
     * Starts the session, attempting to use Redis as the handler if available.
     * Falls back to the default file handler if Redis is not connected.
     */
    public static function start(): void
    {
        // Check if the Redis extension is loaded and if we can connect.
        $cache = Cache::getInstance();

        if ($cache->isConnected()) {
            // Configure PHP to use Redis for session storage.
            // The 'redis' hostname is the service name from docker-compose.yml.
            ini_set('session.save_handler', 'redis');
            ini_set('session.save_path', 'tcp://redis:6379');
        } else {
            // Log that we are falling back to the default handler
            error_log("Redis not connected. Falling back to default session handler.");
        }

        // Start the session only if it's not already active.
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}