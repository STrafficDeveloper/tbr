<?php

declare(strict_types=1);

use App\Core\Request;

/**
 * Tabs as real links, so every tab is its own crawlable URL and works
 * without JavaScript (Aktiviti TBR hub, Hall of Fame profile).
 *
 * @var list<array{label:string,url:string}> $items
 * @var string $label       accessible name for the tab group
 * @var string|null $active url of the tab to highlight; defaults to the current page
 */
$path = (new Request())->path();
$highlight = $active ?? $path;
?>
<nav class="tabs" aria-label="<?= e($label) ?>">
    <ul class="tabs__list">
<?php foreach ($items as $item): ?>
<?php
    $isHighlighted = $highlight === $item['url'];
    // "page" only when the tab really is this page; a preview elsewhere is just "true".
    $ariaCurrent = $item['url'] === $path ? 'page' : 'true';
?>
        <li>
            <a class="tabs__link<?= $isHighlighted ? ' is-active' : '' ?>" href="<?= e($item['url']) ?>"<?= $isHighlighted ? ' aria-current="' . $ariaCurrent . '"' : '' ?>>
                <?= e($item['label']) ?>
            </a>
        </li>
<?php endforeach; ?>
    </ul>
</nav>
