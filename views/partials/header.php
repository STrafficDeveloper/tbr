<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Config;
use App\Core\Request;

/** @var array<int,array{label:string,url:string,children?:array<int,array{label:string,url:string}>}> $nav */
$nav = Config::get('site.nav', []);
$current = (new Request())->path();

$isActive = static function (array $item) use ($current): bool {
    if ($item['url'] === '/') {
        return $current === '/';
    }

    if (str_starts_with($current, $item['url'])) {
        return true;
    }

    foreach ($item['children'] ?? [] as $child) {
        if (str_starts_with($current, $child['url'])) {
            return true;
        }
    }

    return false;
};
?>
<header class="site-header" data-header>
    <div class="site-header__bar">
        <a class="site-header__logo" href="/" aria-label="<?= e((string) Config::get('app.name')) ?> — Laman Utama">
            <img src="/assets/img/logo-tbr.svg" alt="The Bikers Ranger" width="132" height="40">
        </a>

        <nav class="site-nav" aria-label="Navigasi utama">
            <ul class="site-nav__list">
<?php foreach ($nav as $item): ?>
<?php $active = $isActive($item); ?>
                <li class="site-nav__item<?= $item['children'] ?? false ? ' site-nav__item--has-children' : '' ?>">
                    <a class="site-nav__link<?= $active ? ' is-active' : '' ?>"
                       href="<?= e($item['url']) ?>"<?= $active ? ' aria-current="page"' : '' ?>>
                        <?= e($item['label']) ?>
                    </a>
<?php if (!empty($item['children'])): ?>
                    <ul class="site-nav__submenu">
<?php foreach ($item['children'] as $child): ?>
                        <li><a href="<?= e($child['url']) ?>"><?= e($child['label']) ?></a></li>
<?php endforeach; ?>
                    </ul>
<?php endif; ?>
                </li>
<?php endforeach; ?>
            </ul>
        </nav>

        <div class="site-header__auth">
<?php if (Auth::check()): ?>
            <a class="site-header__auth-link" href="/tetapan">PROFILE</a>
            <form class="site-header__logout" method="post" action="/log-keluar">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit">LOG KELUAR</button>
            </form>
<?php else: ?>
            <a class="site-header__auth-link" href="/log-masuk">LOG MASUK</a>
            <span aria-hidden="true">|</span>
            <a class="site-header__auth-link site-header__auth-link--cta" href="/daftar">DAFTAR JADI MEMBER</a>
<?php endif; ?>
        </div>

        <button class="site-header__toggle" type="button"
                data-menu-toggle aria-expanded="false" aria-controls="mobile-nav">
            <span class="site-header__toggle-bars" aria-hidden="true"></span>
            <span class="visually-hidden">Buka menu</span>
        </button>
    </div>

    <div class="mobile-nav" id="mobile-nav" data-mobile-nav hidden>
        <nav aria-label="Navigasi mudah alih">
            <ul class="mobile-nav__list">
<?php foreach ($nav as $index => $item): ?>
<?php if (empty($item['children'])): ?>
                <li><a class="mobile-nav__link" href="<?= e($item['url']) ?>"><?= e($item['label']) ?></a></li>
<?php else: ?>
                <li class="mobile-nav__group">
                    <button class="mobile-nav__link mobile-nav__link--toggle" type="button"
                            data-submenu-toggle aria-expanded="false" aria-controls="submenu-<?= (int) $index ?>">
                        <?= e($item['label']) ?>
                        <span class="mobile-nav__chevron" aria-hidden="true"></span>
                    </button>
                    <ul class="mobile-nav__submenu" id="submenu-<?= (int) $index ?>" hidden>
<?php foreach ($item['children'] as $child): ?>
                        <li><a href="<?= e($child['url']) ?>"><?= e($child['label']) ?></a></li>
<?php endforeach; ?>
                    </ul>
                </li>
<?php endif; ?>
<?php endforeach; ?>
            </ul>

            <div class="mobile-nav__auth">
<?php if (Auth::check()): ?>
                <a href="/tetapan">PROFILE</a>
<?php else: ?>
                <a href="/log-masuk">LOG MASUK</a>
                <span aria-hidden="true">|</span>
                <a href="/daftar">DAFTAR JADI MEMBER</a>
<?php endif; ?>
            </div>
        </nav>
    </div>
</header>
