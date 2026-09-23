<?php

declare(strict_types=1);

/** @var list<array<string,mixed>> $events */
?>
<section class="section" aria-labelledby="page-title">
    <div class="container">
        <?= component('section-heading', [
            'eyebrow' => 'Jadual tour',
            'title' => 'Stop & Tarikh',
            'tag' => 'h1',
            'id' => 'page-title',
            'linkUrl' => '/pit-stop/daftar',
            'linkLabel' => 'Daftar Slot',
        ]) ?>

<?php if ($events === []): ?>
        <?= component('empty-state', ['message' => 'Tiada pit stop akan datang buat masa ini. Nantikan jadual seterusnya!']) ?>
<?php else: ?>
        <h2 class="visually-hidden">Pit stop akan datang</h2>
        <div class="grid">
<?php foreach ($events as $event): ?>
            <?= component('card-event', ['event' => $event]) ?>
<?php endforeach; ?>
        </div>
<?php endif; ?>
    </div>
</section>
