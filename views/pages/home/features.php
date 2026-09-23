<?php

declare(strict_types=1);

/**
 * @var array<string,mixed>|null $hero  hero-of-the-month profile
 * @var array<string,mixed>|null $video latest Panas Atas Jalan video
 */
?>
<?php if ($hero !== null): ?>
<section class="section" aria-labelledby="biker-hero-title">
    <div class="container">
        <?= component('section-heading', ['eyebrow' => 'hall of fame', 'title' => 'Biker Hero', 'id' => 'biker-hero-title']) ?>
        <?= component('feature', [
            'image' => $hero['cover_image'] ?: $hero['avatar'],
            'imageAlt' => (string) $hero['name'],
            'eyebrow' => 'Hero of the Month',
            'title' => (string) $hero['name'],
            'text' => $hero['summary'],
            'linkUrl' => '/hall-of-fame',
        ]) ?>
    </div>
</section>
<?php endif; ?>

<?php if ($video !== null): ?>
<?php
    $thumbnail = $video['thumbnail'];
    if (empty($thumbnail) && $video['provider'] === 'youtube' && !empty($video['video_id'])) {
        $thumbnail = 'https://i.ytimg.com/vi/' . rawurlencode((string) $video['video_id']) . '/hqdefault.jpg';
    }
?>
<section class="section" aria-labelledby="panas-title">
    <div class="container">
        <?= component('section-heading', ['eyebrow' => 'content menarik dari komuniti bikers', 'title' => 'Panas Atas Jalan', 'id' => 'panas-title']) ?>
        <?= component('feature', [
            'image' => $thumbnail,
            'title' => (string) $video['title'],
            'text' => $video['description'],
            'count' => (int) $video['views_count'],
            'countLabel' => 'tontonan',
            'linkUrl' => '/panas-atas-jalan',
            'reverse' => true,
        ]) ?>
    </div>
</section>
<?php endif; ?>
