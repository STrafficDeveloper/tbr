<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/**
 * Only a SHA-256 of each token is stored, so a leaked database backup cannot
 * be used to reset anyone's password.
 */
final class PasswordResetRepository
{
    public const LIFETIME_MINUTES = 60;

    /** Issues a fresh token and voids any earlier unused ones for this member. */
    public function create(int $userId): string
    {
        $token = bin2hex(random_bytes(32));

        Database::execute('DELETE FROM password_resets WHERE user_id = ? AND used_at IS NULL', [$userId]);
        Database::insert(
            'INSERT INTO password_resets (user_id, token_hash, expires_at)
             VALUES (?, ?, NOW() + INTERVAL ' . self::LIFETIME_MINUTES . ' MINUTE)',
            [$userId, hash('sha256', $token)],
        );

        return $token;
    }

    /** @return int|null the member the token belongs to, if it is unused and unexpired */
    public function findValidUserId(string $token): ?int
    {
        $row = Database::selectOne(
            'SELECT user_id FROM password_resets
             WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()
             LIMIT 1',
            [hash('sha256', $token)],
        );

        return $row === null ? null : (int) $row['user_id'];
    }

    /**
     * Spends the token. Returns false if another request spent it first, so
     * two tabs submitting the same link cannot both change the password.
     */
    public function consume(string $token): bool
    {
        return Database::execute(
            'UPDATE password_resets SET used_at = NOW()
             WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()',
            [hash('sha256', $token)],
        ) === 1;
    }
}
