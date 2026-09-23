<?php

declare(strict_types=1);

namespace App\Core;

final class SecurityHeaders
{
    /**
     * The whole site runs on its own files plus YouTube thumbnails and the
     * youtube-nocookie player. Anything else a browser is asked to load (an
     * injected script, a rogue iframe) is refused.
     */
    private const CSP = [
        "default-src 'self'",
        "script-src 'self'",
        "style-src 'self'",
        "img-src 'self' data: https://i.ytimg.com",
        "font-src 'self'",
        "connect-src 'self'",
        'frame-src https://www.youtube-nocookie.com',
        "form-action 'self'",
        "frame-ancestors 'self'",
        "base-uri 'self'",
        "object-src 'none'",
    ];

    public static function send(): void
    {
        header('Content-Security-Policy: ' . implode('; ', self::CSP));
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
        header('Cross-Origin-Opener-Policy: same-origin');

        // Only over HTTPS: sending HSTS on plain HTTP is ignored, and on a
        // local dev box it would pin the browser to https for a year.
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}
