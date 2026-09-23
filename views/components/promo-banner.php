<?php

declare(strict_types=1);

/** @var array<string,mixed> $banner */
$hasImage = !empty($banner['image_desktop']) || !empty($banner['image_mobile']);
?>
<article class="promo-banner<?= $hasImage ? '' : ' promo-banner--plain' ?>">
<?php if ($hasImage): ?>
    <picture class="promo-banner__media">
<?php if (!empty($banner['image_desktop'])): ?>
        <source media="(min-width: 768px)" srcset="<?= e(uploaded($banner['image_desktop'])) ?>">
<?php endif; ?>
        <img src="<?= e(uploaded($banner['image_mobile'] ?: $banner['image_desktop'])) ?>"
             alt="<?= e($banner['alt_text'] ?? '') ?>"
             loading="lazy" decoding="async" width="1064" height="412">
    </picture>
<?php endif; ?>
    <div class="promo-banner__body">
        <h2 class="promo-banner__title"><?= e($banner['title']) ?></h2>
<?php if (!empty($banner['body'])): ?>
        <p class="promo-banner__text"><?= e($banner['body']) ?></p>
<?php endif; ?>
<?php if (!empty($banner['link_url']) && !empty($banner['cta_label'])): ?>
        <a class="btn btn--primary promo-banner__cta" href="<?= e($banner['link_url']) ?>">
            <?= e($banner['cta_label']) ?>
        </a>
<?php endif; ?>
    </div>
</article>
