<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Core\ValidationException;
use PDOException;

final class PitStopRegistrationRepository
{
    /**
     * Books a slot. The event row is locked for the duration, so two riders
     * racing for the last slot can't both get it.
     *
     * @param array<string,mixed> $member the signed-in user (name, phone, email)
     * @throws ValidationException field => message when the booking can't be made
     */
    public function register(string $eventSlug, array $member, string $plate, ?string $state, ?string $consentIp): int
    {
        return Database::transaction(function () use ($eventSlug, $member, $plate, $state, $consentIp): int {
            $event = Database::selectOne(
                'SELECT id, capacity, status, starts_at FROM pitstop_events WHERE slug = ? FOR UPDATE',
                [$eventSlug],
            );

            if ($event === null || $event['status'] !== 'published' || strtotime((string) $event['starts_at']) <= time()) {
                throw new ValidationException(['event' => 'Pendaftaran untuk pit stop ini telah ditutup.']);
            }

            $existing = Database::selectOne(
                'SELECT id FROM pitstop_registrations WHERE event_id = ? AND user_id = ? LIMIT 1',
                [$event['id'], $member['id']],
            );

            if ($existing !== null) {
                throw new ValidationException(['event' => 'Anda sudah mendaftar untuk pit stop ini.']);
            }

            if ($event['capacity'] !== null) {
                $taken = Database::selectOne(
                    'SELECT COUNT(*) AS n FROM pitstop_registrations WHERE event_id = ? AND status <> ?',
                    [$event['id'], 'rejected'],
                );

                if ((int) $taken['n'] >= (int) $event['capacity']) {
                    throw new ValidationException(['event' => 'Maaf, slot untuk pit stop ini sudah penuh.']);
                }
            }

            try {
                return Database::insert(
                    'INSERT INTO pitstop_registrations
                        (event_id, user_id, name, phone, email, plate_no, state, consent_pdpa, consent_at, consent_ip, status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW(), ?, ?)',
                    [
                        $event['id'],
                        $member['id'],
                        $member['name'],
                        $member['phone'],
                        $member['email'],
                        $plate,
                        $state,
                        $consentIp,
                        'pending',
                    ],
                );
            } catch (PDOException $exception) {
                if ($exception->getCode() !== '23000') {
                    throw $exception;
                }

                // A double-submit by the same member trips the member key, not the plate key.
                if (str_contains($exception->getMessage(), 'uq_pitstop_registration_member')) {
                    throw new ValidationException(['event' => 'Anda sudah mendaftar untuk pit stop ini.']);
                }

                throw new ValidationException(['plate' => 'Nombor plat ini sudah didaftarkan untuk pit stop ini.']);
            }
        });
    }

    /** @return array<string,mixed>|null one of this member's registrations, with its event */
    public function findForUser(int $registrationId, int $userId): ?array
    {
        return Database::selectOne(
            'SELECT r.id, r.plate_no, r.status, r.created_at, e.title, e.slug, e.starts_at, e.location_name, e.state
             FROM pitstop_registrations r
             JOIN pitstop_events e ON e.id = r.event_id
             WHERE r.id = ? AND r.user_id = ?
             LIMIT 1',
            [$registrationId, $userId],
        );
    }

    /** @return list<array<string,mixed>> newest event first */
    public function allForUser(int $userId): array
    {
        return Database::select(
            'SELECT r.id, r.plate_no, r.status, r.created_at, e.title, e.slug, e.starts_at, e.location_name, e.state
             FROM pitstop_registrations r
             JOIN pitstop_events e ON e.id = r.event_id
             WHERE r.user_id = ?
             ORDER BY e.starts_at DESC',
            [$userId],
        );
    }
}
