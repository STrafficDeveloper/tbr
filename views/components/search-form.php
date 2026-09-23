<?php

declare(strict_types=1);

/**
 * @var string $action
 * @var string $value        current search term
 * @var string $placeholder
 * @var string $label        visible to screen readers only; the design shows no label
 * @var array<string,string> $keep other filters to carry into the search
 */
$name ??= 'q';
$keep ??= [];
?>
<form class="search-form" action="<?= e($action) ?>" method="get" role="search">
    <label class="visually-hidden" for="search-<?= e($name) ?>"><?= e($label) ?></label>
    <input class="search-form__input" id="search-<?= e($name) ?>" type="search" name="<?= e($name) ?>"
           value="<?= e($value) ?>" placeholder="<?= e($placeholder) ?>" autocomplete="off" enterkeyhint="search">
<?php foreach ($keep as $key => $keptValue): ?>
<?php if ($keptValue !== '' && $key !== $name && $key !== 'page'): ?>
    <input type="hidden" name="<?= e($key) ?>" value="<?= e($keptValue) ?>">
<?php endif; ?>
<?php endforeach; ?>
    <button class="search-form__button" type="submit">
        <?= icon('search') ?>
        <span class="visually-hidden">Cari</span>
    </button>
</form>
