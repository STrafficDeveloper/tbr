<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    private const SESSION_KEY = '_user_id';

    /** @var array<string,mixed>|null */
    private static ?array $cached = null;

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function id(): ?int
    {
        $id = Session::get(self::SESSION_KEY);

        return is_int($id) ? $id : null;
    }

    /** @return array<string,mixed>|null */
    public static function user(): ?array
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        $id = self::id();

        if ($id === null) {
            return null;
        }

        $user = Database::selectOne(
            'SELECT id, name, email, phone, avatar_path, state, plate_no, role, status
             FROM users WHERE id = ? AND status = ? LIMIT 1',
            [$id, 'active'],
        );

        if ($user === null) {
            self::logout();

            return null;
        }

        return self::$cached = $user;
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? null) === 'admin';
    }

    public static function login(int $userId): void
    {
        Session::regenerate();
        Session::put(self::SESSION_KEY, $userId);
        self::$cached = null;
    }

    public static function logout(): void
    {
        Session::forget(self::SESSION_KEY);
        self::$cached = null;
    }

    /** Sends a guest to the login page, remembering where they were headed. */
    public static function requireLogin(Request $request): void
    {
        if (self::check()) {
            return;
        }

        Session::put('_intended', $request->path());
        Response::redirect('/log-masuk');
    }
}
