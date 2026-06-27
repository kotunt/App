<?php

namespace App\Core;

use Redis;
use RedisException;

class RealtimeNotifier
{
    private const CHANNEL = 'realtime_updates';

    /**
     * Publish a message to the real-time channel.
     *
     * @param string $event The name of the event (e.g., 'new_bet').
     * @param array $data The data payload to send.
     * @return bool True on success, false on failure.
     */
    public static function publish(string $event, array $data): bool
    {
        try {
            $redis = Cache::getInstance()->getRedisClient(); // Assuming Cache class has a public getter for the client
            if ($redis && $redis->ping() === '+PONG') {
                $payload = json_encode(['event' => $event, 'data' => $data]);
                $redis->publish(self::CHANNEL, $payload);
                return true;
            }
        } catch (RedisException $e) {
            error_log("RealtimeNotifier failed to publish: " . $e->getMessage());
        }
        return false;
    }
}