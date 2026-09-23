<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const KEY = '_csrf_token';

    public static function token(): string
    {
        $token = Session::get(self::KEY);

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::put(self::KEY, $token);
        }

        return $token;
    }

    public static function field(): string
    {
        return '<input type="hidden" name="' . self::KEY . '" value="' . e(self::token()) . '">';
    }

    public static function isValid(?string $token): bool
    {
        $expected = Session::get(self::KEY);

        return is_string($expected) && is_string($token) && hash_equals($expected, $token);
    }

    public static function fieldName(): string
    {
        return self::KEY;
    }
}
