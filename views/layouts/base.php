<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Seo;

/** @var Seo $seo */
/** @var string $content */
?>
<!DOCTYPE html>
<html lang="<?= e((string) Config::get('app.locale')) ?>">
<head>
<?= \App\Core\View::partial('partials/meta', ['seo' => $seo]) ?>
</head>
<body>
    <a class="skip-link" href="#main">Langkau ke kandungan utama</a>

<?= \App\Core\View::partial('partials/header') ?>

    <main id="main">
<?= $content ?>
    </main>

<?= \App\Core\View::partial('partials/footer') ?>

    <script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
