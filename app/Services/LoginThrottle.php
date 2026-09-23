<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Brute-force protection for the login form, recorded in login_attempts.
 * Limits apply per account (stops guessing one member's password) and per
 * IP (stops one machine spraying many accounts). A successful login resets
 * the per-account count.
 */
final class LoginThrottle
{
    private const WINDOW_MINUTES = 15;
    private const MAX_FAILURES_PER_ACCOUNT = 5;
    private const MAX_FAILURES_PER_IP = 20;

    public function isLocked(string $identifier, string $ip): bool
    {
        $key = $this->key($identifier);

        $account = Database::selectOne(
            'SELECT COUNT(*) AS failures FROM login_attempts
             WHERE identifier = ? AND succeeded = 0
               AND attempted_at > NOW() - INTERVAL ' . self::WINDOW_MINUTES . ' MINUTE
               AND attempted_at > COALESCE(
                   (SELECT MAX(attempted_at) FROM login_attempts WHERE identifier = ? AND succeeded = 1),
                   \'1970-01-01 00:00:00\'
               )',
            [$key, $key],
        );

        if ((int) ($account['failures'] ?? 0) >= self::MAX_FAILURES_PER_ACCOUNT) {
            return true;
        }

        $network = Database::selectOne(
            'SELECT COUNT(*) AS failures FROM login_attempts
             WHERE ip_address = ? AND succeeded = 0
               AND attempted_at > NOW() - INTERVAL ' . self::WINDOW_MINUTES . ' MINUTE',
            [$this->ip($ip)],
        );

        return (int) ($network['failures'] ?? 0) >= self::MAX_FAILURES_PER_IP;
    }

    public function record(string $identifier, string $ip, bool $succeeded): void
    {
        Database::insert(
            'INSERT INTO login_attempts (identifier, ip_address, succeeded) VALUES (?, ?, ?)',
            [$this->key($identifier), $this->ip($ip), (int) $succeeded],
        );

        // Keep a month for investigating abuse, then let it go (PDPA: don't
        // hold IP addresses longer than needed). Occasional, not every login.
        if (random_int(1, 50) === 1) {
            Database::execute('DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL 30 DAY');
        }
    }

    public function windowMinutes(): int
    {
        return self::WINDOW_MINUTES;
    }

    /** Hashed so the audit table never holds members' emails or phone numbers. */
    private function key(string $identifier): string
    {
        return hash('sha256', strtolower(trim($identifier)));
    }

    private function ip(string $ip): string
    {
        return packIp($ip) ?? (string) packIp('0.0.0.0');
    }
}
