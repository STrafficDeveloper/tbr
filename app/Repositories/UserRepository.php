<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class UserRepository
{
    public function emailExists(string $email): bool
    {
        return Database::selectOne('SELECT id FROM users WHERE email = ? LIMIT 1', [$email]) !== null;
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
}
