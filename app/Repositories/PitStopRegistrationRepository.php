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

    /** @return list<array<string,mixed>> */
    public function adminList(?int $eventId, ?string $status, string $search, int $limit, int $offset): array
    {
        [$where, $bindings] = $this->adminFilters($eventId, $status, $search);

        return Database::select(
            "SELECT r.id, r.name, r.phone, r.email, r.plate_no, r.state, r.status, r.created_at, r.consent_at,
                    e.title AS event_title, e.starts_at
             FROM pitstop_registrations r
             JOIN pitstop_events e ON e.id = r.event_id
             WHERE {$where}
             ORDER BY r.created_at DESC
             LIMIT ? OFFSET ?",
            [...$bindings, $limit, $offset],
        );
    }

    public function adminCount(?int $eventId, ?string $status, string $search): int
    {
        [$where, $bindings] = $this->adminFilters($eventId, $status, $search);
        $row = Database::selectOne(
            "SELECT COUNT(*) AS n FROM pitstop_registrations r JOIN pitstop_events e ON e.id = r.event_id WHERE {$where}",
            $bindings,
        );

        return (int) ($row['n'] ?? 0);
    }

    /** @return array<string,int> status => count, for the dashboard */
    public function countsByStatus(): array
    {
        $rows = Database::select('SELECT status, COUNT(*) AS n FROM pitstop_registrations GROUP BY status');

        return array_map('intval', array_column($rows, 'n', 'status'));
    }

    /** @return array<string,mixed>|null the registration with its event, for the approval email */
    public function findWithEvent(int $id): ?array
    {
        return Database::selectOne(
            'SELECT r.*, e.title AS event_title, e.starts_at, e.location_name
             FROM pitstop_registrations r JOIN pitstop_events e ON e.id = r.event_id
             WHERE r.id = ?',
            [$id],
        );
    }

    public function setStatus(int $id, string $status): void
    {
        Database::execute(
            'UPDATE pitstop_registrations SET status = ?, reviewed_at = NOW() WHERE id = ?',
            [$status, $id],
        );
    }

    /** @return array{0:string,1:list<string|int>} */
    private function adminFilters(?int $eventId, ?string $status, string $search): array
    {
        $where = ['1 = 1'];
        $bindings = [];

        if ($eventId !== null) {
            $where[] = 'r.event_id = ?';
            $bindings[] = $eventId;
        }

        if ($status !== null) {
            $where[] = 'r.status = ?';
            $bindings[] = $status;
        }

        if ($search !== '') {
            $like = '%' . addcslashes($search, '%_\\') . '%';
            $where[] = '(r.name LIKE ? OR r.phone LIKE ? OR r.plate_no LIKE ? OR r.email LIKE ?)';
            array_push($bindings, $like, $like, $like, $like);
        }

        return [implode(' AND ', $where), $bindings];
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
