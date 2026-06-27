<?php

namespace App\Core;

use Redis;
use RedisException;

class Cache
{
    private static ?Cache $instance = null;
    private ?Redis $redis = null;

    private function __construct()
    {
        if (class_exists('Redis')) {
            try {
                $this->redis = new Redis();
                // Use 'redis' as the hostname, which is the service name in docker-compose
                $this->redis->connect('redis', 6379);
            } catch (RedisException $e) {
                $this->redis = null;
                // Log the error but don't kill the application
                error_log("Redis connection failed: " . $e->getMessage());
            }
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $key)
    {
        if ($this->redis === null) return false;
        $value = $this->redis->get($key);
        return $value ? unserialize($value) : false;
    }

    public function set(string $key, $value, int $ttl = 3600): bool
    {
        if ($this->redis === null) return false;
        return $this->redis->setex($key, $ttl, serialize($value));
    }

    public function delete(string $key): int|bool
    {
        if ($this->redis === null) return false;
        return $this->redis->del($key);
    }

    public function isConnected(): bool
    {
        return $this->redis !== null && $this->redis->ping() === '+PONG';
    }
}