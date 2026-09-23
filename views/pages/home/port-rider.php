<?php

declare(strict_types=1);

use App\Core\Config;

/**
 * Preview of the directory. Search and state filters submit to /port-rider,
 * the one canonical listing, rather than creating filtered copies of home.
 *
 * @var list<array<string,mixed>> $places
 * @var list<string> $states
 * @var list<int> $likedIds
 */
$stateNames = Config::get('site.states', []);
$options = ['' => 'All'];
foreach ($states as $key) {
    $options[$key] = $stateNames[$key] ?? $key;
}
?>
<section class="section" aria-labelledby="port-rider-title">
    <div class="container">
        <?= component('section-heading', [
            'eyebrow' => 'bike shop & pitstop',
            'title' => 'Port Rider',
            'id' => 'port-rider-title',
            'linkUrl' => '/port-rider',
            'linkLabel' => 'Lihat Semua',
        ]) ?>
        <?= component('search-form', ['action' => '/port-rider', 'value' => '', 'placeholder' => 'Search', 'label' => 'Cari bike shop atau pit stop']) ?>
        <?= component('filter-chips', ['options' => $options, 'param' => 'negeri', 'active' => '', 'path' => '/port-rider', 'label' => 'Tapis mengikut negeri']) ?>

<?php if ($places === []): ?>
        <?= component('empty-state', ['message' => 'Senarai Port Rider akan dikemas kini tidak lama lagi.']) ?>
<?php else: ?>
        <div class="grid grid--rail">
<?php foreach ($places as $place): ?>
            <?= component('card-port-rider', ['place' => $place, 'liked' => in_array((int) $place['id'], $likedIds, true)]) ?>
<?php endforeach; ?>
        </div>
<?php endif; ?>
    </div>
</section>
