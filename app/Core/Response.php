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

    public static function back(): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $path = parse_url((string) $referer, PHP_URL_PATH);

        self::redirect(is_string($path) ? $path : '/');
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
        echo View::render('pages/errors/404', ['seo' => (new Seo())->setTitle('Halaman Tidak Dijumpai')]);
        exit;
    }
}
