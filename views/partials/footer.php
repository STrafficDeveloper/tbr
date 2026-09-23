<?php

declare(strict_types=1);

use App\Core\Config;
use App\Repositories\SiteRepository;

$site = Config::get('site');
$settings = new SiteRepository();

// Only links an admin has filled in; an empty group is left out entirely.
$socialGroups = [];
foreach ($site['socials'] as $group) {
    $links = [];
    foreach ($group['links'] as $platform => $link) {
        $url = $settings->setting($link['setting']);
        if ($url !== '') {
            $links[$platform] = ['label' => $link['label'], 'url' => $url];
        }
    }
    if ($links !== []) {
        $socialGroups[] = ['label' => $group['label'], 'links' => $links];
    }
}
?>
<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__brand">
            <div class="site-footer__lockup">
                <img src="/assets/img/logo-tbr.svg" alt="The Bikers Ranger" width="120" height="36" loading="lazy">
                <span class="site-footer__x" aria-hidden="true">x</span>
                <img src="/assets/img/logo-raja-kapcai.svg" alt="Raja Kapcai" width="120" height="36" loading="lazy">
            </div>
            <p class="site-footer__partnership"><?= e($site['partnership']) ?></p>
            <p class="site-footer__company"><?= e($site['company']) ?></p>
        </div>

        <nav class="site-footer__nav" aria-label="Navigasi footer">
            <h2 class="site-footer__heading">Navigasi</h2>
            <ul>
<?php foreach ($site['footer_nav'] as $label => $url): ?>
                <li><a href="<?= e($url) ?>"><?= e($label) ?></a></li>
<?php endforeach; ?>
            </ul>
        </nav>

<?php foreach ($socialGroups as $group): ?>
        <div class="site-footer__socials">
            <h2 class="site-footer__heading"><?= e($group['label']) ?></h2>
            <ul>
<?php foreach ($group['links'] as $platform => $link): ?>
                <li>
                    <a href="<?= e($link['url']) ?>" rel="noopener noreferrer" target="_blank">
                        <?= icon($platform) ?>
                        <span><?= e($link['label']) ?></span>
                    </a>
                </li>
<?php endforeach; ?>
            </ul>
        </div>
<?php endforeach; ?>

        <div class="site-footer__contact">
            <h2 class="site-footer__heading">Hubungi Kami</h2>
            <ul>
                <li><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $site['phone']) ?? '') ?>"><?= e($site['phone']) ?></a></li>
                <li><a href="mailto:<?= e($site['email']) ?>"><?= e($site['email']) ?></a></li>
            </ul>
        </div>
    </div>

    <div class="site-footer__legal">
        <p>
            &copy; <?= date('Y') ?> <?= e($site['company']) ?>. Hak Cipta Terpelihara.
            <?= e($site['privacy_note']) ?>
        </p>
    </div>
</footer>
