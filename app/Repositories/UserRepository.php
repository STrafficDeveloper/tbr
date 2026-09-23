<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class UserRepository
{
    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        return Database::selectOne(
            'SELECT id, name, email, phone, state, avatar_path, password_hash, role, status
             FROM users WHERE id = ? LIMIT 1',
            [$id],
        );
    }

    /**
     * Members sign in with either their email or their phone number.
     *
     * @return array<string,mixed>|null
     */
    public function findByLogin(string $identifier): ?array
    {
        $column = str_contains($identifier, '@') ? 'email' : 'phone';

        return Database::selectOne(
            "SELECT id, name, email, phone, password_hash, role, status
             FROM users WHERE {$column} = ? LIMIT 1",
            [$identifier],
        );
    }

    /** @return array<string,mixed>|null */
    public function findByEmail(string $email): ?array
    {
        return Database::selectOne(
            'SELECT id, name, email, status FROM users WHERE email = ? LIMIT 1',
            [$email],
        );
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        return $this->exists('email', $email, $exceptId);
    }

    public function phoneExists(string $phone, ?int $exceptId = null): bool
    {
        return $this->exists('phone', $phone, $exceptId);
    }

    /**
     * @param array{name:string,email:string,password:string,phone:string,state:string,
     *              follows_tbr:bool,follows_raja_kapcai:bool,whatsapp_opt_in:bool,
     *              contest_opt_in:bool,consent_ip:?string} $data
     */
    public function createMember(array $data): int
    {
        return Database::insert(
            'INSERT INTO users
                (name, email, password_hash, phone, state, role, status,
                 follows_tbr, follows_raja_kapcai, whatsapp_opt_in, contest_opt_in, consent_at, consent_ip)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)',
            [
                $data['name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['phone'],
                $data['state'],
                'member',
                'active',
                (int) $data['follows_tbr'],
                (int) $data['follows_raja_kapcai'],
                (int) $data['whatsapp_opt_in'],
                (int) $data['contest_opt_in'],
                $data['consent_ip'],
            ],
        );
    }

    /** @param array{name:string,email:string,phone:string,state:string} $data */
    public function updateProfile(int $id, array $data): void
    {
        Database::execute(
            'UPDATE users SET name = ?, email = ?, phone = ?, state = ? WHERE id = ?',
            [$data['name'], $data['email'], $data['phone'], $data['state'], $id],
        );
    }

    public function updatePassword(int $id, string $password): void
    {
        Database::execute(
            'UPDATE users SET password_hash = ? WHERE id = ?',
            [password_hash($password, PASSWORD_DEFAULT), $id],
        );
    }

    public function updateAvatar(int $id, string $path): void
    {
        Database::execute('UPDATE users SET avatar_path = ? WHERE id = ?', [$path, $id]);
    }

    private function exists(string $column, string $value, ?int $exceptId): bool
    {
        $sql = "SELECT id FROM users WHERE {$column} = ?";
        $bindings = [$value];

        if ($exceptId !== null) {
            $sql .= ' AND id <> ?';
            $bindings[] = $exceptId;
        }

        return Database::selectOne($sql . ' LIMIT 1', $bindings) !== null;
    }
}
