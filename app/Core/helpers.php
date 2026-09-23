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

function uploaded(?string $path, string $fallback = '/assets/img/placeholder.svg'): string
{
    if ($path === null || $path === '') {
        return $fallback;
    }

    return '/uploads/' . ltrim($path, '/');
}

/** The value a member typed before a failed submit, so the form is not wiped. */
function old(string $key, string $default = ''): string
{
    $values = Session::getFlash('_old', []);

    return is_array($values) && isset($values[$key]) && is_string($values[$key])
        ? $values[$key]
        : $default;
}

/** The validation message for one field from the last failed submit. */
function error(string $field): ?string
{
    $errors = Session::getFlash('_errors', []);

    return is_array($errors) && isset($errors[$field]) ? (string) $errors[$field] : null;
}

function component(string $name, array $data = []): string
{
    return \App\Core\View::partial('components/' . $name, $data);
}

/** Inline reference to a symbol in the shared SVG sprite; decorative by default. */
function icon(string $name, string $class = 'icon'): string
{
    return '<svg class="' . e($class) . '" aria-hidden="true" focusable="false">'
        . '<use href="' . e(asset('img/icons.svg')) . '#' . e($name) . '"></use></svg>';
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
