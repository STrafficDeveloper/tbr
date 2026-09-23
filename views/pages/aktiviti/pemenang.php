<?php

declare(strict_types=1);

use App\Core\View;

/** @var list<array<string,mixed>> $contests rounds whose winners are public */
?>
<section class="section" aria-labelledby="page-title">
    <div class="container">
        <?= View::partial('partials/aktiviti-header', ['title' => 'Pentas Juara: Senarai Pemenang']) ?>

<?php if ($contests === []): ?>
        <?= component('empty-state', [
            'message' => 'Pemenang akan diumumkan selepas setiap peraduan tamat. Sementara itu, jom sertai peraduan!',
            'actionUrl' => '/aktiviti/peraduan',
            'actionLabel' => 'Lihat Peraduan',
        ]) ?>
<?php else: ?>
        <h2 class="winners-hub__title">Tahniah Semua Pemenang!</h2>
        <div class="grid">
<?php foreach ($contests as $contest): ?>
            <?= component('card-post', [
                'url' => '/aktiviti/pemenang/' . rawurlencode((string) $contest['slug']),
                'title' => (string) $contest['title'],
                'image' => $contest['cover_image'],
                'meta' => formatDateRange((string) $contest['starts_on'], (string) $contest['ends_on']),
                'badge' => 'Pemenang',
            ]) ?>
<?php endforeach; ?>
        </div>
<?php endif; ?>
    </div>
</section>
