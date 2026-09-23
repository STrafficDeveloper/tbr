<?php

declare(strict_types=1);

/** @var string $backUrl the referring page on this site, or "/" */
?>
<section class="container error-page">
    <h1>Sesi Telah Tamat</h1>
    <p>Borang ini telah dibuka terlalu lama atau sesi anda telah tamat. Sila kembali, muat semula halaman dan cuba lagi.</p>
    <a class="btn btn--primary" href="<?= e($backUrl) ?>">Kembali</a>
</section>
