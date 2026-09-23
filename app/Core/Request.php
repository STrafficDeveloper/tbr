<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    /** @var array<string,string> */
    private array $routeParams = [];

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = is_string($path) ? $path : '/';

        return '/' . trim($path, '/');
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function input(string $key, ?string $default = null): ?string
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? null;

        if (!is_string($value)) {
            return $default;
        }

        $value = trim($value);

        return $value === '' ? $default : $value;
    }

    /** Untrimmed POST value, for passwords: spaces are part of the secret. */
    public function raw(string $key): string
    {
        $value = $_POST[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    public function has(string $key): bool
    {
        return isset($_POST[$key]) || isset($_GET[$key]);
    }

    public function file(string $key): ?array
    {
        $file = $_FILES[$key] ?? null;

        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        return $file;
    }

    public function ip(): string
    {
        return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    /** @param array<string,string> $params */
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function routeParam(string $key, ?string $default = null): ?string
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function page(): int
    {
        return max(1, (int) ($this->input('page') ?? 1));
    }
}
