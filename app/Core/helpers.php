<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Session;

/** Escapes a value for HTML output. Every echoed variable goes through this. */
function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/'): string
{
    return rtrim((string) Config::get('app.url'), '/') . '/' . ltrim($path, '/');
}

/** Cache-busted asset URL so deploys never serve a stale CSS/JS file. */
function asset(string $path): string
{
    $path = '/assets/' . ltrim($path, '/');
    $file = BASE_PATH . '/public' . $path;
    $version = is_file($file) ? (string) filemtime($file) : '1';

    return $path . '?v=' . $version;
}

function uploaded(?string $path, string $fallback = '/assets/img/placeholder.jpg'): string
{
    if ($path === null || $path === '') {
        return $fallback;
    }

    return '/uploads/' . ltrim($path, '/');
}

function old(string $key, string $default = ''): string
{
    $values = Session::get('_old', []);

    return is_array($values) && isset($values[$key]) && is_string($values[$key])
        ? $values[$key]
        : $default;
}

/** Formats a date for display in Bahasa Malaysia pages. */
function formatDate(?string $date, string $format = 'j M Y'): string
{
    if ($date === null || $date === '') {
        return '';
    }

    $timestamp = strtotime($date);

    return $timestamp === false ? '' : date($format, $timestamp);
}

function formatCount(int $count): string
{
    if ($count >= 1000000) {
        return rtrim(rtrim(number_format($count / 1000000, 1), '0'), '.') . 'm';
    }

    if ($count >= 1000) {
        return rtrim(rtrim(number_format($count / 1000, 1), '0'), '.') . 'k';
    }

    return (string) $count;
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

    return trim($value, '-');
}
