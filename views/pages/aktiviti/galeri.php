<?php

declare(strict_types=1);

use App\Core\View;

/** @var list<array<string,mixed>> $albums */
?>
<section class="section" aria-labelledby="page-title">
    <div class="container">
        <?= View::partial('partials/aktiviti-header', ['title' => 'Tonton & Tengok Sendiri']) ?>

<?php if ($albums === []): ?>
        <?= component('empty-state', ['message' => 'Galeri akan dikemas kini tidak lama lagi.']) ?>
<?php else: ?>
        <h2 class="visually-hidden">Album galeri</h2>
        <div class="grid">
<?php foreach ($albums as $album): ?>
<?php $count = (int) $album['images_count']; ?>
            <?= component('card-post', [
                'url' => '/aktiviti/galeri/' . rawurlencode((string) $album['slug']),
                'title' => (string) $album['title'],
                'image' => $album['cover_image'] ?: $album['first_image'],
                'excerpt' => $album['description'],
                'meta' => $count > 0 ? number_format($count) . ' gambar' : null,
                'likes' => (int) $album['likes_count'],
                'views' => (int) $album['views_count'],
            ]) ?>
<?php endforeach; ?>
        </div>
<?php endif; ?>
    </div>
</section>
