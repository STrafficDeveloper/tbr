<?php

declare(strict_types=1);

/**
 * Generic content card for gallery albums and contests: image, title,
 * short copy, an optional date line and the likes/views row. The title link
 * is stretched over the whole card so the card is one tap target.
 *
 * @var string $url
 * @var string $title
 * @var string|null $image    upload path
 * @var string|null $excerpt
 * @var string|null $meta     e.g. "(1 Okt – 31 Okt 2026)"
 * @var string|null $badge    e.g. "Sedang Berlangsung"
 * @var int|null $likes
 * @var int|null $views
 */
$image ??= null;
$excerpt ??= null;
$meta ??= null;
$badge ??= null;
$likes ??= null;
$views ??= null;
?>
<article class="card card--post">
    <div class="card__media">
        <img src="<?= e(uploaded($image)) ?>" alt="" loading="lazy" decoding="async" width="400" height="260">
<?php if ($badge !== null): ?>
        <span class="card__badge"><?= e($badge) ?></span>
<?php endif; ?>
    </div>
    <div class="card__body">
        <h3 class="card__title">
            <a class="card__link" href="<?= e($url) ?>"><?= e($title) ?></a>
        </h3>
<?php if ($meta !== null): ?>
        <p class="card__meta"><?= icon('calendar', 'icon icon--sm') ?> <?= e($meta) ?></p>
<?php endif; ?>
<?php if ($excerpt !== null): ?>
        <p class="card__excerpt"><?= e($excerpt) ?></p>
<?php endif; ?>
<?php if ($likes !== null): ?>
        <?= component('counts', ['likes' => $likes, 'views' => $views]) ?>
<?php endif; ?>
    </div>
</article>
