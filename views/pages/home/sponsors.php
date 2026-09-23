<?php

declare(strict_types=1);

/**
 * Image-only partner strip. Outbound partner links carry rel="sponsored" so
 * search engines don't count them as editorial endorsements.
 *
 * @var list<array<string,mixed>> $banners
 */
if ($banners === []) {
    return;
}
?>
<section class="sponsors" aria-label="Rakan kongsi">
    <div class="container sponsors__list">
<?php foreach ($banners as $banner): ?>
<?php
    $picture = '<picture>'
        . (!empty($banner['image_desktop'])
            ? '<source media="(min-width: 768px)" srcset="' . e(uploaded($banner['image_desktop'])) . '">'
            : '')
        . '<img src="' . e(uploaded($banner['image_mobile'] ?: $banner['image_desktop'])) . '"'
        . ' alt="' . e($banner['alt_text'] ?: $banner['title']) . '"'
        . ' width="1064" height="379" loading="lazy" decoding="async">'
        . '</picture>';
?>
<?php if (!empty($banner['link_url'])): ?>
        <a class="sponsors__item" href="<?= e($banner['link_url']) ?>" target="_blank" rel="sponsored noopener noreferrer"><?= $picture ?></a>
<?php else: ?>
        <div class="sponsors__item"><?= $picture ?></div>
<?php endif; ?>
<?php endforeach; ?>
    </div>
</section>
