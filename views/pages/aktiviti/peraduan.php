<?php

declare(strict_types=1);

use App\Core\View;
use App\Repositories\ContestRepository;

/** @var list<array<string,mixed>> $contests */
$phaseLabels = ['ongoing' => 'Sedang Berlangsung', 'upcoming' => 'Akan Datang', 'ended' => 'Tamat'];
?>
<section class="section" aria-labelledby="page-title">
    <div class="container">
        <?= View::partial('partials/aktiviti-header', ['title' => 'Gegar Jalan, Takluk Cabaran!']) ?>

<?php if ($contests === []): ?>
        <?= component('empty-state', ['message' => 'Peraduan baharu akan diumumkan tidak lama lagi.']) ?>
<?php else: ?>
        <h2 class="visually-hidden">Senarai peraduan</h2>
        <div class="grid">
<?php foreach ($contests as $contest): ?>
            <?= component('card-post', [
                'url' => '/aktiviti/peraduan/' . rawurlencode((string) $contest['slug']),
                'title' => (string) $contest['title'],
                'image' => $contest['cover_image'],
                'excerpt' => $contest['tagline'],
                'meta' => formatDateRange((string) $contest['starts_on'], (string) $contest['ends_on']),
                'badge' => $phaseLabels[ContestRepository::phase($contest)],
            ]) ?>
<?php endforeach; ?>
        </div>
<?php endif; ?>
    </div>
</section>
