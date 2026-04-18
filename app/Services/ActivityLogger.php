<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    public static function grade(string $action, int $userId, array $context = []): void
    {
        Log::channel('business')->info("GRADE:{$action}", array_merge(['user_id' => $userId], $context));
    }

    public static function auth(string $action, int $userId, string $ip): void
    {
        Log::channel('business')->info("AUTH:{$action}", [
            'user_id' => $userId,
            'ip' => $ip,
        ]);
    }

    public static function error(string $context, \Throwable $e): void
    {
        Log::channel('business')->error("ERROR:{$context}", [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }
}
