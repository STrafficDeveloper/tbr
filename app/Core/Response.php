<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function redirect(string $path, int $status = 302): never
    {
        header('Location: ' . $path, true, $status);
        exit;
    }

    /** Returns to the referring page on this site, filters and all. */
    public static function back(string $fallback = '/', string $fragment = ''): never
    {
        self::redirect(self::safeReferer($fallback) . ($fragment !== '' ? '#' . rawurlencode($fragment) : ''));
    }

    /**
     * The referring page's path and query, if it is on this site. Only those
     * parts are reused, so a forged Referer can't send anyone elsewhere.
     */
    public static function safeReferer(string $fallback = '/'): string
    {
        $parts = parse_url((string) ($_SERVER['HTTP_REFERER'] ?? ''));
        // HTTP_HOST carries the port when it isn't 80/443, so compare like with like.
        $refererHost = is_array($parts) && isset($parts['host'])
            ? $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '')
            : null;
        $sameHost = $refererHost !== null && strcasecmp($refererHost, (string) ($_SERVER['HTTP_HOST'] ?? '')) === 0;
        $path = is_array($parts) ? ($parts['path'] ?? '') : '';

        return $sameHost && str_starts_with($path, '/') && !str_starts_with($path, '//')
            ? $path . (isset($parts['query']) ? '?' . $parts['query'] : '')
            : $fallback;
    }

    public static function wantsJson(): bool
    {
        return str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
    }

    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function notFound(): never
    {
        http_response_code(404);
        echo View::render('pages/errors/404', ['seo' => (new Seo())->setTitle('Halaman Tidak Dijumpai')->noIndex()]);
        exit;
    }
}
