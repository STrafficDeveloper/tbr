<?php

declare(strict_types=1);

/**
 * The views row under a card. Numbers are abbreviated for the eye ("14k")
 * but the full figure is kept for screen readers.
 *
 * @var int $views
 */

// A zero reads as broken; show nothing until there is something to count.
if ($views === 0) {
    return;
}
?>
<ul class="counts">
    <li class="counts__item">
        <?= icon('eye', 'icon icon--sm') ?>
        <span aria-hidden="true"><?= e(formatCount($views)) ?></span>
        <span class="visually-hidden"><?= number_format($views) ?> tontonan</span>
    </li>
</ul>
