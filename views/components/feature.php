<?php

declare(strict_types=1);

/**
 * Large image beside a text card (home: Biker Hero, Panas Atas Jalan).
 * The two blocks mirror each other in the design, hence $reverse.
 *
 * @var string|null $image      upload path
 * @var string|null $imageAlt
 * @var string|null $eyebrow
 * @var string $title
 * @var string|null $text
 * @var int|null $count         shown with $countLabel for screen readers
 * @var string|null $countLabel e.g. "tontonan"
 * @var string $linkUrl
 * @var string|null $linkLabel
 * @var bool|null $reverse      card first, image second
 */
$image ??= null;
$imageAlt ??= '';
$eyebrow ??= null;
$text ??= null;
$count ??= null;
$countLabel ??= '';
$linkLabel ??= 'View More';
$reverse ??= false;
?>
<article class="feature<?= $reverse ? ' feature--reverse' : '' ?>">
    <div class="feature__media">
        <img src="<?= e(uploaded($image)) ?>" alt="<?= e($imageAlt) ?>" loading="lazy" decoding="async" width="640" height="535">
    </div>
    <div class="feature__card">
<?php if ($count !== null && $count > 0): ?>
        <p class="feature__count">
            <?= icon('eye', 'icon icon--sm') ?>
            <span aria-hidden="true"><?= e(formatCount($count)) ?></span>
            <span class="visually-hidden"><?= number_format($count) ?> <?= e($countLabel) ?></span>
        </p>
<?php endif; ?>
<?php if ($eyebrow !== null): ?>
        <p class="feature__eyebrow"><?= e($eyebrow) ?></p>
<?php endif; ?>
        <h3 class="feature__title"><?= e($title) ?></h3>
<?php if ($text !== null && $text !== ''): ?>
        <p class="feature__text"><?= e($text) ?></p>
<?php endif; ?>
        <a class="btn btn--ghost feature__link" href="<?= e($linkUrl) ?>">
            <?= e($linkLabel) ?>
            <span class="visually-hidden">: <?= e($title) ?></span>
        </a>
    </div>
</article>
