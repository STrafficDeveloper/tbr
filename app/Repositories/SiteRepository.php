<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

/** Admin-editable site copy (settings table) and the home page counters. */
final class SiteRepository
{
    /** @var array<string,string>|null shared per request: the page, footer and hero all read it */
    private static ?array $settings = null;

    public function setting(string $key, string $default = ''): string
    {
        if (self::$settings === null) {
            $rows = Database::select('SELECT setting_key, setting_value FROM settings');
            self::$settings = array_column($rows, 'setting_value', 'setting_key');
        }

        $value = self::$settings[$key] ?? null;

        return $value === null || $value === '' ? $default : (string) $value;
    }

    /** @return list<array{value:string,label:string}> */
    public function stats(): array
    {
        return Database::select('SELECT value, label FROM site_stats ORDER BY sort_order, id');
    }
}
