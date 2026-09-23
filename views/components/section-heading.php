<?php

declare(strict_types=1);

/**
 * The eyebrow + title pair used at the top of every page and section
 * (e.g. "aktiviti TBR" / "Tonton & tengok sendiri").
 *
 * @var string $title
 * @var string|null $eyebrow
 * @var string|null $id       lets a <section> point aria-labelledby at the title
 * @var string|null $tag      h1 for the page heading, h2 (default) for sections
 * @var string|null $linkUrl
 * @var string|null $linkLabel
 */
$eyebrow ??= null;
$id ??= null;
$tag = in_array($tag ?? 'h2', ['h1', 'h2', 'h3'], true) ? ($tag ?? 'h2') : 'h2';
$linkUrl ??= null;
$linkLabel ??= null;
?>
<div class="section-heading">
    <div class="section-heading__text">
<?php if ($eyebrow !== null): ?>
        <p class="section-heading__eyebrow"><?= e($eyebrow) ?></p>
<?php endif; ?>
        <<?= $tag ?> class="section-heading__title"<?= $id !== null ? ' id="' . e($id) . '"' : '' ?>><?= e($title) ?></<?= $tag ?>>
    </div>
<?php if ($linkUrl !== null && $linkLabel !== null): ?>
    <a class="section-heading__link" href="<?= e($linkUrl) ?>">
        <?= e($linkLabel) ?> <?= icon('chevron-right', 'icon icon--sm') ?>
    </a>
<?php endif; ?>
</div>
