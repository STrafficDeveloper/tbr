<?php

declare(strict_types=1);

use App\Core\View;

/**
 * @var array<string,mixed> $profile
 * @var string $tab konten | galeri | biodata
 * @var list<array{label:string,url:string}> $tabs
 * @var list<array<string,mixed>> $sections
 * @var list<array<string,mixed>> $images
 * @var list<array<string,mixed>> $videos
 */
$followUrl = $profile['instagram_url'] ?: ($profile['facebook_url'] ?: $profile['tiktok_url']);
?>
<section class="section" aria-labelledby="page-title">
    <div class="container">
        <header class="profile">
            <img class="profile__avatar" src="<?= e(uploaded($profile['avatar'] ?? null, '/assets/img/avatar-placeholder.svg')) ?>"
                 alt="<?= e((string) $profile['name']) ?>" width="160" height="160">
            <div class="profile__text">
                <p class="section-heading__eyebrow">Hall of Fame &middot; Hero of the Month</p>
                <h1 class="profile__name" id="page-title"><?= e($profile['name']) ?></h1>
<?php if (!empty($profile['handle'])): ?>
                <p class="profile__handle"><?= e($profile['handle']) ?></p>
<?php endif; ?>
<?php if (!empty($profile['headline'])): ?>
                <p class="profile__headline"><?= e($profile['headline']) ?></p>
<?php endif; ?>
            </div>
<?php if (!empty($followUrl)): ?>
            <a class="btn btn--primary profile__follow" href="<?= e($followUrl) ?>" target="_blank" rel="noopener noreferrer">
                Follow<span class="visually-hidden"> <?= e($profile['name']) ?> (dibuka di tab baharu)</span>
            </a>
<?php endif; ?>
        </header>

        <?= component('tabs', ['label' => 'Profil ' . $profile['name'], 'items' => $tabs]) ?>

<?php if ($tab === 'biodata'): ?>
<?php if ($sections === []): ?>
        <?= component('empty-state', ['message' => 'Biodata akan dikemas kini tidak lama lagi.']) ?>
<?php else: ?>
        <div class="biodata">
<?php foreach ($sections as $section): ?>
            <section class="biodata__block">
                <h2><?= e($section['heading']) ?></h2>
                <p><?= nl2br(e((string) $section['body'])) ?></p>
            </section>
<?php endforeach; ?>
        </div>
<?php endif; ?>

<?php elseif ($tab === 'konten'): ?>
<?php if ($videos === []): ?>
        <?= component('empty-state', ['message' => 'Video akan dimuat naik tidak lama lagi.']) ?>
<?php else: ?>
        <h2 class="visually-hidden">Video</h2>
        <div class="grid grid--videos">
<?php foreach ($videos as $video): ?>
            <?= component('card-video', ['video' => $video]) ?>
<?php endforeach; ?>
        </div>
<?php endif; ?>

<?php else: ?>
<?php if ($images === []): ?>
        <?= component('empty-state', ['message' => 'Galeri akan dimuat naik tidak lama lagi.']) ?>
<?php else: ?>
        <h2 class="visually-hidden">Galeri</h2>
        <div class="photo-grid">
<?php foreach ($images as $image): ?>
            <?= component('photo-tile', ['path' => (string) $image['path'], 'alt' => (string) ($image['alt_text'] ?: $profile['name'])]) ?>
<?php endforeach; ?>
        </div>
<?php endif; ?>
<?php endif; ?>
    </div>
</section>
<?php if ($tab !== 'biodata'): ?>
<?= View::partial('partials/lightbox') ?>
<?php endif; ?>
