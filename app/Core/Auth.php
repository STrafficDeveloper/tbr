<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    private const SESSION_KEY = '_user_id';
    private const FINGERPRINT_KEY = '_auth_fp';

    /** @var array<string,mixed>|null */
    private static ?array $cached = null;

    /** True only for a live session on an active account, checked against the database. */
    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?int
    {
        return self::check() ? (int) self::$cached['id'] : null;
    }

    /** @return array<string,mixed>|null */
    public static function user(): ?array
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        $id = Session::get(self::SESSION_KEY);

        if (!is_int($id)) {
            return null;
        }

        $user = Database::selectOne(
            'SELECT id, name, email, phone, avatar_path, state, plate_no, role, status, password_hash
             FROM users WHERE id = ? AND status = ? LIMIT 1',
            [$id, 'active'],
        );

        // A password change or reset ends every other session: theirs carries
        // a fingerprint of the old hash, which no longer matches.
        $fingerprint = Session::get(self::FINGERPRINT_KEY);

        if ($user === null || !is_string($fingerprint)
            || !hash_equals(self::fingerprint((string) $user['password_hash']), $fingerprint)) {
            self::logout();

            return null;
        }

        unset($user['password_hash']);

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
        self::refreshFingerprint($userId);
    }

    /**
     * Re-pins this session to the member's current password, so the one who
     * just changed it stays signed in while every other session is dropped.
     */
    public static function refreshFingerprint(int $userId): void
    {
        $row = Database::selectOne('SELECT password_hash FROM users WHERE id = ?', [$userId]);
        Session::put(self::FINGERPRINT_KEY, self::fingerprint((string) ($row['password_hash'] ?? '')));
        self::$cached = null;
    }

    public static function logout(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::forget(self::FINGERPRINT_KEY);
        self::$cached = null;
    }

    private static function fingerprint(string $passwordHash): string
    {
        return hash('sha256', $passwordHash);
    }

    /** Sends a guest to the login page, remembering where they were headed. */
    public static function requireLogin(Request $request): void
    {
        if (self::check()) {
            return;
        }

        $query = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
        Session::put('_intended', $request->path() . ($query !== '' ? '?' . $query : ''));
        Response::redirect('/log-masuk');
    }

    /** Login and password-reset pages make no sense once signed in. */
    public static function requireGuest(): void
    {
        if (self::check()) {
            Response::redirect('/tetapan');
        }
    }

    /**
     * Where to go after logging in. Only same-site paths are honoured:
     * "//evil.example" or "https://…" would turn login into an open redirect.
     */
    public static function pullIntendedUrl(string $default): string
    {
        $intended = Session::get('_intended');
        Session::forget('_intended');

        if (!is_string($intended) || !str_starts_with($intended, '/') || str_starts_with($intended, '//')
            || str_contains($intended, '\\')) {
            return $default;
        }

        return $intended;
    }
}
