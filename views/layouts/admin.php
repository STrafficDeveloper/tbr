<?php

declare(strict_types=1);

use App\Admin\Resources;
use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Seo;
use App\Core\View;

/**
 * @var Seo $seo
 * @var string $content
 * @var string $pageTitle
 */
$path = (new Request())->path();
$member = Auth::user();

$nav = [
    ['Papan Pemuka', '/admin'],
    ['Pendaftaran', '/admin/pendaftaran'],
    ['Ahli', '/admin/ahli'],
];
$contentNav = [];
foreach (Resources::all() as $resource) {
    if (!$resource->isChild()) {
        $contentNav[] = [$resource->label, '/admin/urus/' . $resource->key];
    }
}

$isActive = static fn (string $url): bool => $url === '/admin' ? $path === '/admin' : str_starts_with($path, $url);
?>
<!DOCTYPE html>
<html lang="ms-MY">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($seo->title()) ?></title>
    <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin">
    <a class="skip-link" href="#main">Langkau ke kandungan utama</a>

    <aside class="admin-nav" aria-label="Menu admin">
        <p class="admin-nav__brand"><a href="/admin">TBR Admin</a></p>
        <nav>
            <ul class="admin-nav__list">
<?php foreach ($nav as [$label, $url]): ?>
                <li><a class="admin-nav__link<?= $isActive($url) ? ' is-active' : '' ?>" href="<?= e($url) ?>"<?= $isActive($url) ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach; ?>
            </ul>
            <p class="admin-nav__heading">Kandungan</p>
            <ul class="admin-nav__list">
<?php foreach ($contentNav as [$label, $url]): ?>
                <li><a class="admin-nav__link<?= $isActive($url) ? ' is-active' : '' ?>" href="<?= e($url) ?>"<?= $isActive($url) ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach; ?>
                <li><a class="admin-nav__link<?= $isActive('/admin/tetapan') ? ' is-active' : '' ?>" href="/admin/tetapan">Tetapan Laman</a></li>
            </ul>
        </nav>
        <div class="admin-nav__footer">
            <p><?= e((string) ($member['name'] ?? '')) ?></p>
            <a href="/" target="_blank" rel="noopener">Lihat laman ↗</a>
            <form method="post" action="/log-keluar">
                <?= Csrf::field() ?>
                <button type="submit">Log keluar</button>
            </form>
        </div>
    </aside>

    <main class="admin-main" id="main">
        <h1 class="admin-main__title"><?= e($pageTitle) ?></h1>
<?= View::partial('partials/flash') ?>
<?= $content ?>
    </main>

    <script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>
</html>
