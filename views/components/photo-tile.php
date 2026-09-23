<?php

declare(strict_types=1);

/**
 * A gallery thumbnail that links to the full photo. With JavaScript it opens
 * in the page's lightbox instead of navigating away.
 *
 * @var string $path     upload path
 * @var string|null $alt
 * @var string|null $caption
 */
$alt ??= '';
$caption ??= '';
$src = uploaded($path);
?>
<figure class="photo-tile">
    <a class="photo-tile__link" href="<?= e($src) ?>" data-lightbox-image data-caption="<?= e($caption ?: $alt) ?>">
        <img src="<?= e($src) ?>" alt="<?= e($alt) ?>" loading="lazy" decoding="async" width="400" height="400">
    </a>
<?php if ($caption !== ''): ?>
    <figcaption class="photo-tile__caption"><?= e($caption) ?></figcaption>
<?php endif; ?>
</figure>
