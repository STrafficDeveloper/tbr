<?php

declare(strict_types=1);

use App\Core\Config;
?>
<section class="hero">
    <div class="container hero__inner">
        <h1 class="hero__title"><?= e((string) Config::get('site.tagline')) ?></h1>
        <p class="hero__lead">
            Satu jalan, satu minat. Jom lepak, sembang dan kongsi cerita tentang perjalanan engkorang.
        </p>
        <div class="hero__actions">
            <a class="btn btn--primary" href="/pit-stop/daftar">Daftar Sekarang</a>
            <a class="btn btn--ghost" href="/port-rider">Cari Port Rider</a>
        </div>
    </div>
</section>
