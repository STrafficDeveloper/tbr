<?php

declare(strict_types=1);

use App\Core\Config;

/** @var list<array<string,mixed>> $events */
?>
<section class="section" aria-labelledby="highlights-title">
    <div class="container">
        <?= component('section-heading', ['eyebrow' => 'aktiviti TBR', 'title' => 'Highlight Bikers', 'id' => 'highlights-title']) ?>
        <?= component('tabs', ['label' => 'Aktiviti TBR', 'items' => Config::get('site.aktiviti_tabs', []), 'active' => '/aktiviti/event']) ?>

<?php if ($events === []): ?>
        <?= component('empty-state', ['message' => 'Tiada pit stop akan datang buat masa ini. Nantikan jadual seterusnya!']) ?>
<?php else: ?>
        <div class="grid grid--rail">
<?php foreach ($events as $event): ?>
            <?= component('card-event', ['event' => $event]) ?>
<?php endforeach; ?>
        </div>
<?php endif; ?>
    </div>
</section>
