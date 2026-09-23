<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class ContestRepository
{
    /** Winners go public on announce_on (or straight away if no date is set). */
    private const ANNOUNCED = '(c.announce_on IS NULL OR c.announce_on <= CURDATE())
                               AND EXISTS (SELECT 1 FROM contest_winners w WHERE w.contest_id = c.id)';

    /** @return list<array<string,mixed>> newest round first */
    public function published(): array
    {
        return Database::select(
            'SELECT c.id, c.title, c.slug, c.tagline, c.cover_image, c.starts_on, c.ends_on, c.announce_on
             FROM contests c
             WHERE c.status = ?
             ORDER BY c.starts_on DESC',
            ['published'],
        );
    }

    /** @return array<string,mixed>|null */
    public function findPublishedBySlug(string $slug): ?array
    {
        return Database::selectOne(
            'SELECT c.id, c.title, c.slug, c.tagline, c.eligibility, c.rules, c.cover_image, c.starts_on, c.ends_on,
                    c.announce_on, c.whatsapp_number, c.whatsapp_message,
                    (' . self::ANNOUNCED . ') AS winners_announced
             FROM contests c
             WHERE c.slug = ? AND c.status = ?
             LIMIT 1',
            [$slug, 'published'],
        );
    }

    /** @return list<array<string,mixed>> rounds whose winners are public, newest first */
    public function withAnnouncedWinners(): array
    {
        return Database::select(
            'SELECT c.id, c.title, c.slug, c.tagline, c.cover_image, c.starts_on, c.ends_on, c.announce_on
             FROM contests c
             WHERE c.status = ? AND ' . self::ANNOUNCED . '
             ORDER BY c.starts_on DESC',
            ['published'],
        );
    }

    /** @return list<array<string,mixed>> */
    public function prizes(int $contestId): array
    {
        return Database::select(
            'SELECT id, rank_label, prize_name, prize_value, image FROM contest_prizes WHERE contest_id = ? ORDER BY sort_order, id',
            [$contestId],
        );
    }

    /**
     * @return array{podium:list<array<string,mixed>>,consolation:list<array<string,mixed>>}
     */
    public function winners(int $contestId): array
    {
        $rows = Database::select(
            'SELECT w.id, w.name, w.bike, w.plate_masked, w.photo, w.position, w.is_consolation,
                    p.rank_label, p.prize_name, p.prize_value
             FROM contest_winners w
             LEFT JOIN contest_prizes p ON p.id = w.prize_id
             WHERE w.contest_id = ?
             ORDER BY w.is_consolation, w.position IS NULL, w.position, w.sort_order, w.id',
            [$contestId],
        );

        return [
            'podium' => array_values(array_filter($rows, static fn (array $r): bool => !$r['is_consolation'])),
            'consolation' => array_values(array_filter($rows, static fn (array $r): bool => (bool) $r['is_consolation'])),
        ];
    }

    /** "ongoing", "upcoming" or "ended", from today's date. */
    public static function phase(array $contest): string
    {
        $today = date('Y-m-d');

        return match (true) {
            $today < $contest['starts_on'] => 'upcoming',
            $today > $contest['ends_on'] => 'ended',
            default => 'ongoing',
        };
    }
}
