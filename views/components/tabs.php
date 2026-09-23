<?php

declare(strict_types=1);

use App\Core\Request;

/**
 * Tabs as real links, so every tab is its own crawlable URL and works
 * without JavaScript (Aktiviti TBR hub, Hall of Fame profile).
 *
 * @var list<array{label:string,url:string}> $items
 * @var string $label accessible name for the tab group
 */
$current = (new Request())->path();
?>
<nav class="tabs" aria-label="<?= e($label) ?>">
    <ul class="tabs__list">
<?php foreach ($items as $item): ?>
<?php $active = $current === $item['url']; ?>
        <li>
            <a class="tabs__link<?= $active ? ' is-active' : '' ?>" href="<?= e($item['url']) ?>"<?= $active ? ' aria-current="page"' : '' ?>>
                <?= e($item['label']) ?>
            </a>
        </li>
<?php endforeach; ?>
    </ul>
</nav>
