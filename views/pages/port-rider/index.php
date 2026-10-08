<?php

declare(strict_types=1);

use App\Core\Paginator;

/**
 * @var list<array<string,mixed>> $places
 * @var Paginator $paginator
 * @var string $state
 * @var string $stateLabel
 * @var array<string,string> $stateOptions
 * @var string $term
 */
$query = ['negeri' => $state, 'q' => $term];
$summary = number_format($paginator->total) . ' lokasi'
    . ($stateLabel !== '' ? " di {$stateLabel}" : '')
    . ($term !== '' ? " untuk carian \"{$term}\"" : '');
?>
<section class="section" aria-labelledby="page-title">
    <div class="container">
        <?= component('section-heading', ['eyebrow' => 'bike shop & pitstop', 'title' => 'Port Rider', 'tag' => 'h1', 'id' => 'page-title']) ?>

        <?= component('search-form', ['action' => '/port-rider', 'value' => $term, 'placeholder' => 'Search', 'label' => 'Cari bike shop atau pit stop', 'keep' => $query]) ?>
        <?= component('filter-chips', ['options' => $stateOptions, 'param' => 'negeri', 'active' => $state, 'path' => '/port-rider', 'query' => $query, 'label' => 'Tapis mengikut negeri']) ?>

        <p class="results-summary" role="status"><?= e($summary) ?></p>

<?php if ($places === []): ?>
        <?= component('empty-state', [
            'message' => 'Tiada lokasi dijumpai. Cuba kata carian lain atau pilih negeri berbeza.',
            'actionUrl' => '/port-rider',
            'actionLabel' => 'Lihat Semua',
        ]) ?>
<?php else: ?>
        <h2 class="visually-hidden">Senarai lokasi</h2>
        <div class="grid">
<?php foreach ($places as $place): ?>
            <?= component('card-port-rider', ['place' => $place]) ?>
<?php endforeach; ?>
        </div>
        <?= component('pagination', ['paginator' => $paginator]) ?>
<?php endif; ?>
    </div>
</section>
