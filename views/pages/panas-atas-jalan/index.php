<?php

declare(strict_types=1);

use App\Core\Paginator;
use App\Core\View;

/**
 * @var list<array<string,mixed>> $videos
 * @var Paginator $paginator
 */
?>
<section class="section" aria-labelledby="page-title">
    <div class="container">
        <?= component('section-heading', [
            'eyebrow' => 'content menarik dari komuniti bikers',
            'title' => 'Panas Atas Jalan',
            'tag' => 'h1',
            'id' => 'page-title',
        ]) ?>

<?php if ($videos === []): ?>
        <?= component('empty-state', ['message' => 'Video baharu akan dimuat naik tidak lama lagi.']) ?>
<?php else: ?>
        <h2 class="visually-hidden">Video</h2>
        <div class="grid grid--videos">
<?php foreach ($videos as $video): ?>
            <?= component('card-video', ['video' => $video]) ?>
<?php endforeach; ?>
        </div>
        <?= component('pagination', ['paginator' => $paginator]) ?>
<?php endif; ?>
    </div>
</section>
<?= View::partial('partials/lightbox') ?>
