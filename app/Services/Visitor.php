<?php

declare(strict_types=1);

namespace App\Services;

/**
 * An anonymous, per-browser id so guests can like things once each.
 * A cookie rather than the IP address: Malaysian mobile carriers put many
 * phones behind one shared IP, which would make one person's like block
 * everyone else's. Only a hash of the id is ever stored.
 */
final class Visitor
{
    private const COOKIE = 'tbr_vid';

    /** @return string sha256 of this browser's id, created on first use */
    public static function hash(): string
    {
        $id = $_COOKIE[self::COOKIE] ?? '';

        if (!is_string($id) || preg_match('/^[a-f0-9]{32}$/', $id) !== 1) {
            $id = bin2hex(random_bytes(16));
            setcookie(self::COOKIE, $id, [
                'expires' => time() + 60 * 60 * 24 * 365,
                'path' => '/',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            $_COOKIE[self::COOKIE] = $id;
        }

        return hash('sha256', $id);
    }

    /** The existing hash without setting a cookie, for read-only page views. */
    public static function existingHash(): ?string
    {
        $id = $_COOKIE[self::COOKIE] ?? '';

        return is_string($id) && preg_match('/^[a-f0-9]{32}$/', $id) === 1 ? hash('sha256', $id) : null;
    }
}
