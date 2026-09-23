<?php

declare(strict_types=1);

/**
 * The design's "Kembali" link at the top of every detail view.
 *
 * @var string $url
 * @var string|null $label
 */
$label ??= 'Kembali';
?>
<a class="back-link" href="<?= e($url) ?>"><?= icon('chevron-left', 'icon icon--sm') ?> <?= e($label) ?></a>
