<?php

declare(strict_types=1);

use App\Core\View;

/** @var list<array<string,mixed>> $events */
?>
<section class="section" aria-labelledby="page-title">
    <div class="container">
        <?= View::partial('partials/aktiviti-header', ['title' => 'Tonton & Tengok Sendiri']) ?>

<?php if ($events === []): ?>
        <?= component('empty-state', ['message' => 'Tiada event akan datang buat masa ini. Nantikan jadual seterusnya!']) ?>
<?php else: ?>
        <h2 class="visually-hidden">Event akan datang</h2>
        <div class="grid">
<?php foreach ($events as $event): ?>
            <?= component('card-event', ['event' => $event]) ?>
<?php endforeach; ?>
        </div>
<?php endif; ?>
    </div>
</section>
