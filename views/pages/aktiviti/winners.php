<?php

declare(strict_types=1);

/**
 * The design's pemenang pop-up: a podium for the top three, then a table of
 * consolation winners.
 *
 * @var array<string,mixed> $contest
 * @var array{podium:list<array<string,mixed>>,consolation:list<array<string,mixed>>} $winners
 */
?>
<article class="section" aria-labelledby="page-title">
    <div class="container">
        <?= component('back-link', ['url' => '/aktiviti/pemenang']) ?>
        <?= component('section-heading', ['eyebrow' => 'aktiviti TBR', 'title' => 'Pemenang ' . $contest['title'], 'tag' => 'h1', 'id' => 'page-title']) ?>
        <p class="winners__cheer">Tahniah Semua Pemenang!</p>

<?php if ($winners['podium'] !== []): ?>
        <h2 class="visually-hidden">Pemenang utama</h2>
        <ol class="podium">
<?php foreach ($winners['podium'] as $winner): ?>
            <li class="podium__place podium__place--<?= (int) ($winner['position'] ?? 0) ?>">
                <img class="podium__photo" src="<?= e(uploaded($winner['photo'] ?? null, '/assets/img/avatar-placeholder.svg')) ?>"
                     alt="" width="160" height="160" loading="lazy">
<?php if (!empty($winner['rank_label'])): ?>
                <p class="podium__rank"><?= e($winner['rank_label']) ?></p>
<?php endif; ?>
                <p class="podium__name"><?= e($winner['name']) ?></p>
                <dl class="podium__details">
<?php if (!empty($winner['bike'])): ?>
                    <div><dt>Bike</dt><dd><?= e($winner['bike']) ?></dd></div>
<?php endif; ?>
<?php if (!empty($winner['plate_masked'])): ?>
                    <div><dt>Plate no</dt><dd><?= e($winner['plate_masked']) ?></dd></div>
<?php endif; ?>
                </dl>
<?php if (!empty($winner['prize_name'])): ?>
                <p class="podium__prize"><?= e($winner['prize_name']) ?><?= !empty($winner['prize_value']) ? '<br><strong>' . e($winner['prize_value']) . '</strong>' : '' ?></p>
<?php endif; ?>
            </li>
<?php endforeach; ?>
        </ol>
<?php endif; ?>

<?php if ($winners['consolation'] !== []): ?>
        <section class="consolation" aria-labelledby="consolation-title">
            <h2 class="consolation__title" id="consolation-title">Hadiah Saguhati</h2>
            <table class="consolation__table">
                <thead>
                    <tr><th scope="col">No</th><th scope="col">Nama</th></tr>
                </thead>
                <tbody>
<?php foreach ($winners['consolation'] as $i => $winner): ?>
                    <tr><td><?= $i + 1 ?></td><td><?= e($winner['name']) ?></td></tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </section>
<?php endif; ?>
    </div>
</article>
