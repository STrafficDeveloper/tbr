<?php

declare(strict_types=1);

use App\Core\Config;

/**
 * The "aktiviti TBR" heading and Event / Galeri / Peraduan / Pemenang tabs
 * shared by every hub page.
 *
 * @var string $title e.g. "Gegar Jalan, Takluk Cabaran!"
 */
?>
<?= component('section-heading', ['eyebrow' => 'aktiviti TBR', 'title' => $title, 'tag' => 'h1', 'id' => 'page-title']) ?>
<?= component('tabs', ['label' => 'Aktiviti TBR', 'items' => Config::get('site.aktiviti_tabs', [])]) ?>
