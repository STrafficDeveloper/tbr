<?php

declare(strict_types=1);

use App\Core\View;

/**
 * @var array<string,mixed> $album
 * @var list<array<string,mixed>> $albums siblings, shown as tabs like the design
 * @var list<array<string,mixed>> $images
 */
$albumTabs = array_map(
    static fn (array $a): array => ['label' => (string) $a['title'], 'url' => '/aktiviti/galeri/' . rawurlencode((string) $a['slug'])],
    $albums,
);
?>
<section class="section" aria-labelledby="page-title">
    <div class="container">
        <?= component('back-link', ['url' => '/aktiviti/galeri']) ?>
        <?= component('section-heading', ['eyebrow' => 'aktiviti TBR', 'title' => (string) $album['title'], 'tag' => 'h1', 'id' => 'page-title']) ?>
<?php if (count($albumTabs) > 1): ?>
        <?= component('tabs', ['label' => 'Album galeri', 'items' => $albumTabs]) ?>
<?php endif; ?>
<?php if (!empty($album['description'])): ?>
        <p class="lead-text"><?= e((string) $album['description']) ?></p>
<?php endif; ?>

<?php if ($images === []): ?>
        <?= component('empty-state', ['message' => 'Gambar untuk album ini akan dimuat naik tidak lama lagi.']) ?>
<?php else: ?>
        <h2 class="visually-hidden">Gambar</h2>
        <div class="photo-grid">
<?php foreach ($images as $image): ?>
            <?= component('photo-tile', [
                'path' => (string) $image['path'],
                'alt' => (string) ($image['alt_text'] ?: $album['title']),
                'caption' => (string) ($image['caption'] ?? ''),
            ]) ?>
<?php endforeach; ?>
        </div>
<?php endif; ?>
    </div>
</section>
<?= View::partial('partials/lightbox') ?>
