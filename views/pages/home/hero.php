<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;
use App\Core\Csrf;
use App\Core\Session;

/**
 * @var \App\Repositories\SiteRepository $site
 * @var list<array<string,mixed>> $slides
 * @var list<array{value:string,label:string}> $stats
 */
$status = Session::getFlash('_signup_status');
$member = Auth::user();
$riderImage = is_file(BASE_PATH . '/public/assets/img/hero-rider.webp') ? '/assets/img/hero-rider.webp' : null;
$points = [
    'Jemputan diperlukan - tiada walk-in',
    'Maklumat lokasi & masa dihantar melalui WhatsApp',
    'Event komuniti - bukan race, bukan rally',
];
?>
<section class="hero" aria-labelledby="hero-title">
<?php if ($slides !== []): ?>
    <div class="hero__backdrop" data-hero-slides>
<?php foreach ($slides as $i => $slide): ?>
        <picture class="hero__slide<?= $i === 0 ? ' is-active' : '' ?>">
<?php if (!empty($slide['image_desktop'])): ?>
            <source media="(min-width: 768px)" srcset="<?= e(uploaded($slide['image_desktop'])) ?>">
<?php endif; ?>
            <img src="<?= e(uploaded($slide['image_mobile'] ?: $slide['image_desktop'])) ?>" alt=""
                 width="1440" height="936" decoding="async"
                 <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
        </picture>
<?php endforeach; ?>
    </div>
<?php endif; ?>

    <div class="container hero__grid">
        <div class="hero__intro">
            <h1 class="hero__title" id="hero-title"><?= e($site->setting('home_hero_title', (string) Config::get('site.tagline'))) ?></h1>
<?php if ($stats !== []): ?>
            <ul class="stats" aria-label="The Bikers Ranger dalam angka">
<?php foreach ($stats as $stat): ?>
                <li class="stats__item">
                    <strong class="stats__value"><?= e($stat['value']) ?></strong>
                    <span class="stats__label"><?= e($stat['label']) ?></span>
                </li>
<?php endforeach; ?>
            </ul>
<?php endif; ?>
        </div>

<?php if ($riderImage !== null): ?>
        <?php /* lazy: it is hidden on phones, and lazy images that are never shown are never fetched. */ ?>
        <img class="hero__rider" src="<?= e($riderImage) ?>" alt="" width="434" height="765" loading="lazy" decoding="async">
<?php endif; ?>

        <div class="hero__signup" id="daftar">
            <div class="signup-card">
<?php if (is_array($status) && !empty($status['message'])): ?>
                <p class="signup-card__status signup-card__status--<?= $status['type'] === 'error' ? 'error' : 'success' ?>"
                   role="<?= $status['type'] === 'error' ? 'alert' : 'status' ?>">
                    <?= e((string) $status['message']) ?>
                </p>
<?php endif; ?>

<?php if ($member !== null): ?>
                <h2 class="signup-card__title">Hai <?= e((string) $member['name']) ?>, jom tempah slot!</h2>
                <p class="signup-card__lead">Pilih pit stop dan daftar nombor plat motor anda untuk sahkan tempat.</p>
                <a class="btn btn--primary signup-card__submit" href="/pit-stop/daftar">Daftar Slot Pit Stop</a>
<?php else: ?>
                <h2 class="signup-card__title">Daftar Slot Pitstop Anda Sekarang!</h2>
                <p class="signup-card__lead">Isi maklumat di bawah untuk terima jemputan melalui WhatsApp</p>

                <form class="form signup-card__form" method="post" action="/daftar">
                    <?= Csrf::field() ?>

                    <?= component('form/input', ['name' => 'name', 'label' => 'Nama Penuh', 'required' => true,
                        'placeholder' => 'cth: Ahmad bin Abdullah', 'autocomplete' => 'name']) ?>
                    <?= component('form/input', ['name' => 'phone', 'label' => 'Nombor Telefon', 'type' => 'tel',
                        'required' => true, 'placeholder' => 'cth: 015-558-8645', 'autocomplete' => 'tel',
                        'inputmode' => 'tel', 'hint' => 'Nombor WhatsApp untuk terima jemputan.']) ?>
                    <?= component('form/input', ['name' => 'email', 'label' => 'Alamat E-mel', 'type' => 'email',
                        'required' => true, 'placeholder' => 'cth: abu@example.com', 'autocomplete' => 'email']) ?>
                    <?= component('form/input', ['name' => 'password', 'label' => 'Kata Laluan', 'type' => 'password',
                        'required' => true, 'autocomplete' => 'new-password', 'hint' => 'Sekurang-kurangnya 8 aksara.']) ?>
                    <?= component('form/select', ['name' => 'state', 'label' => 'Negeri', 'required' => true,
                        'placeholder' => 'Pilih negeri korang', 'options' => Config::get('site.states', [])]) ?>

                    <fieldset class="signup-card__consents">
                        <legend class="visually-hidden">Pilihan tambahan</legend>
                        <?= component('form/checkbox', ['name' => 'follows_tbr', 'label' => 'Saya follow The Bikers Ranger (FB/IG/TikTok)']) ?>
                        <?= component('form/checkbox', ['name' => 'follows_raja_kapcai', 'label' => 'Saya follow Raja Kapcai (FB/IG/TikTok)']) ?>
                        <?= component('form/checkbox', ['name' => 'whatsapp_opt_in', 'label' => 'Saya bersetuju terima update event melalui WhatsApp']) ?>
                        <?= component('form/checkbox', ['name' => 'contest_opt_in', 'label' => 'Saya bersetuju untuk menyertai peraduan dan acara konvoi']) ?>
                    </fieldset>

                    <div class="hp" aria-hidden="true">
                        <label for="field-website">Website</label>
                        <input id="field-website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>

                    <button class="btn btn--primary signup-card__submit" type="submit">Daftar Sekarang!</button>
                    <p class="signup-card__privacy"><?= e((string) Config::get('site.privacy_note')) ?></p>
                </form>
<?php endif; ?>
            </div>

            <ul class="hero__points">
<?php foreach ($points as $point): ?>
                <li><?= icon('chevron-right', 'icon icon--sm') ?> <?= e($point) ?></li>
<?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>
