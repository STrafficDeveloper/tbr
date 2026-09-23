<?php

declare(strict_types=1);

/**
 * The likes / views row under every card. Numbers are abbreviated for the eye
 * ("14k") but the full figure is kept for screen readers.
 *
 * @var int $likes
 * @var int|null $views
 */
$views ??= null;

// A row of zeros reads as broken; show nothing until there is something to count.
if ($likes === 0 && !$views) {
    return;
}
?>
<ul class="counts">
    <li class="counts__item">
        <?= icon('heart', 'icon icon--sm') ?>
        <span aria-hidden="true"><?= e(formatCount($likes)) ?></span>
        <span class="visually-hidden"><?= number_format($likes) ?> suka</span>
    </li>
<?php if ($views !== null): ?>
    <li class="counts__item">
        <?= icon('eye', 'icon icon--sm') ?>
        <span aria-hidden="true"><?= e(formatCount($views)) ?></span>
        <span class="visually-hidden"><?= number_format($views) ?> tontonan</span>
    </li>
<?php endif; ?>
</ul>
