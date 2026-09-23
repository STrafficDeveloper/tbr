<?php

declare(strict_types=1);

/**
 * A video tile (Panas Atas Jalan, Hall of Fame content). It links straight to
 * the video so it works with no JavaScript; data-video-* lets a script open it
 * inline instead. A video with no source yet renders as a plain tile.
 *
 * @var array<string,mixed> $video a videos row
 */
$videoId = (string) ($video['video_id'] ?? '');
$isYoutube = ($video['provider'] ?? '') === 'youtube' && $videoId !== '';
$href = $isYoutube ? 'https://www.youtube.com/watch?v=' . rawurlencode($videoId) : (string) ($video['video_url'] ?? '');

$thumbnail = uploaded($video['thumbnail'] ?? null);
if (empty($video['thumbnail']) && $isYoutube) {
    $thumbnail = 'https://i.ytimg.com/vi/' . rawurlencode($videoId) . '/hqdefault.jpg';
}

$media = '<img src="' . e($thumbnail) . '" alt="" loading="lazy" decoding="async" width="480" height="270">'
    . '<span class="card__play">' . icon('play', 'icon icon--xl') . '</span>';
?>
<article class="card card--video">
<?php if ($href !== ''): ?>
    <a class="card__media card__media--video" href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer"<?= $isYoutube ? ' data-video-provider="youtube" data-video-id="' . e($videoId) . '"' : '' ?>>
        <?= $media ?>
        <span class="visually-hidden">Tonton video: <?= e($video['title']) ?> (dibuka di tab baharu)</span>
    </a>
<?php else: ?>
    <div class="card__media card__media--video"><?= $media ?></div>
<?php endif; ?>
    <div class="card__body">
        <h3 class="card__title"><?= e($video['title']) ?></h3>
    </div>
</article>
