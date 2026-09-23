<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Request;
use App\Core\Validator;

/** Site copy and social links stored in the settings table. */
final class SettingsController extends AdminController
{
    /** key => [label, type, hint]; only these keys can ever be written. */
    public const FIELDS = [
        'home_hero_title' => ['Tajuk hero laman utama', 'text', 'Tajuk utama (H1) laman utama.'],
        'home_gathering_title' => ['Tajuk jalur kuning', 'text', 'cth: Bukan race. Bukan rally.'],
        'home_gathering_highlight' => ['Tajuk jalur kuning (baris kedua)', 'text', 'cth: Ini gathering.'],
        'pitstop_intro' => ['Teks "Ukhwah Biker"', 'textarea', ''],
        'social_tbr_facebook' => ['Facebook The Bikers Ranger', 'url', ''],
        'social_tbr_instagram' => ['Instagram The Bikers Ranger', 'url', ''],
        'social_tbr_tiktok' => ['TikTok The Bikers Ranger', 'url', ''],
        'social_rk_facebook' => ['Facebook Raja Kapcai', 'url', ''],
        'social_rk_instagram' => ['Instagram Raja Kapcai', 'url', ''],
        'social_rk_tiktok' => ['TikTok Raja Kapcai', 'url', ''],
    ];

    public function edit(Request $request): string
    {
        $rows = Database::select('SELECT setting_key, setting_value FROM settings');

        return $this->adminView('settings', 'Tetapan Laman', [
            'fields' => self::FIELDS,
            'values' => array_column($rows, 'setting_value', 'setting_key'),
        ]);
    }

    public function update(Request $request): never
    {
        $this->verifyCsrf($request);

        $rules = [];
        $labels = [];
        foreach (self::FIELDS as $key => [$label, $type]) {
            $rules[$key] = $type === 'url' ? 'url|max:500' : 'max:2000';
            $labels[$key] = $label;
        }

        $validator = new Validator($_POST, $rules, $labels);

        if (!$validator->passes()) {
            $this->backWithErrors('/admin/tetapan', $validator->errors(), array_map(
                static fn (mixed $v): ?string => is_string($v) ? $v : null,
                array_intersect_key($_POST, self::FIELDS),
            ));
        }

        foreach ($validator->validated() as $key => $value) {
            Database::execute(
                'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
                [$key, $value],
            );
        }

        $this->redirectWithStatus('/admin/tetapan', 'Tetapan telah disimpan.');
    }
}
