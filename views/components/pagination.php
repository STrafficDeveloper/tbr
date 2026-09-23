<?php

declare(strict_types=1);

use App\Core\Paginator;

/** @var Paginator $paginator */
if (!$paginator->hasPages()) {
    return;
}
?>
<nav class="pagination" aria-label="Halaman">
    <ul class="pagination__list">
        <li>
<?php if ($paginator->page > 1): ?>
            <a class="pagination__link" href="<?= e($paginator->url($paginator->page - 1)) ?>" rel="prev">
                <?= icon('chevron-left', 'icon icon--sm') ?><span class="visually-hidden">Sebelumnya</span>
            </a>
<?php else: ?>
            <span class="pagination__link is-disabled" aria-hidden="true"><?= icon('chevron-left', 'icon icon--sm') ?></span>
<?php endif; ?>
        </li>
<?php foreach ($paginator->window() as $page): ?>
        <li>
<?php if ($page === null): ?>
            <span class="pagination__gap" aria-hidden="true">&hellip;</span>
<?php elseif ($page === $paginator->page): ?>
            <span class="pagination__link is-active" aria-current="page"><?= $page ?></span>
<?php else: ?>
            <a class="pagination__link" href="<?= e($paginator->url($page)) ?>">
                <span class="visually-hidden">Halaman </span><?= $page ?>
            </a>
<?php endif; ?>
        </li>
<?php endforeach; ?>
        <li>
<?php if ($paginator->page < $paginator->lastPage): ?>
            <a class="pagination__link" href="<?= e($paginator->url($paginator->page + 1)) ?>" rel="next">
                <?= icon('chevron-right', 'icon icon--sm') ?><span class="visually-hidden">Seterusnya</span>
            </a>
<?php else: ?>
            <span class="pagination__link is-disabled" aria-hidden="true"><?= icon('chevron-right', 'icon icon--sm') ?></span>
<?php endif; ?>
        </li>
    </ul>
</nav>
