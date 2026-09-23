<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Seo;

/** @var Seo $seo */
?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($seo->title()) ?></title>
    <meta name="description" content="<?= e($seo->description()) ?>">
    <meta name="robots" content="<?= e($seo->robots()) ?>">
    <link rel="canonical" href="<?= e($seo->canonical()) ?>">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e((string) Config::get('app.name')) ?>">
    <meta property="og:locale" content="ms_MY">
    <meta property="og:title" content="<?= e($seo->title()) ?>">
    <meta property="og:description" content="<?= e($seo->description()) ?>">
    <meta property="og:url" content="<?= e($seo->canonical()) ?>">
    <meta property="og:image" content="<?= e($seo->image()) ?>">
    <meta name="twitter:card" content="summary_large_image">

    <meta name="theme-color" content="#101010">
    <link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">

<?php foreach ($seo->jsonLd() as $schema): ?>
    <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
<?php endforeach; ?>
