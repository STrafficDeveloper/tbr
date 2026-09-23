<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Sliding-window throttle backed by the rate_limit_hits table, so limits hold
 * across PHP workers on shared hosting where there is no Redis or APCu.
 */
final class RateLimiter
{
    /** Buckets are hashed so raw IPs and emails are never stored here. */
    public static function bucket(string $action, string $identity): string
    {
        return $action . ':' . hash('sha256', $identity);
    }

    public function tooManyAttempts(string $bucket, int $maxAttempts, int $windowSeconds): bool
    {
        $row = Database::selectOne(
            'SELECT COUNT(*) AS hits FROM rate_limit_hits
             WHERE bucket = ? AND hit_at > (NOW() - INTERVAL ? SECOND)',
            [$bucket, $windowSeconds],
        );

        return (int) ($row['hits'] ?? 0) >= $maxAttempts;
    }

    public function hit(string $bucket): void
    {
        Database::insert('INSERT INTO rate_limit_hits (bucket) VALUES (?)', [$bucket]);

        // Prune occasionally instead of on every request; nothing needs a day-old hit.
        if (random_int(1, 50) === 1) {
            Database::execute('DELETE FROM rate_limit_hits WHERE hit_at < (NOW() - INTERVAL 1 DAY)');
        }
    }
}
