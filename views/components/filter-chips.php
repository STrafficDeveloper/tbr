<?php

declare(strict_types=1);

/**
 * Filter pills rendered as links (?negeri=perak) so each filtered list is a
 * shareable, indexable URL and needs no JavaScript.
 *
 * @var array<string,string> $options value => label; '' is the "All" option
 * @var string $param         query-string key, e.g. "negeri"
 * @var string $active        currently selected value
 * @var string $path          listing path the links point at
 * @var array<string,string> $query other filters to keep, e.g. the search term
 * @var string $label         accessible name for the group
 */
$query ??= [];
?>
<nav class="chips" aria-label="<?= e($label) ?>">
    <ul class="chips__list">
<?php foreach ($options as $value => $optionLabel): ?>
<?php
    $params = array_filter(array_merge($query, [$param => (string) $value]), static fn (string $v): bool => $v !== '');
    unset($params['page']);
    $href = $path . ($params === [] ? '' : '?' . http_build_query($params));
    $isActive = (string) $value === $active;
?>
        <li>
            <a class="chip<?= $isActive ? ' is-active' : '' ?>" href="<?= e($href) ?>"<?= $isActive ? ' aria-current="true"' : '' ?>>
                <?= e($optionLabel) ?>
            </a>
        </li>
<?php endforeach; ?>
    </ul>
</nav>
