<?php

declare(strict_types=1);

/** @var array<string,mixed>|null $member */
?>
<section class="section" aria-labelledby="page-title">
    <div class="container register">
        <div class="register-card register-card--done">
            <span class="register-card__tick" aria-hidden="true"><?= icon('check') ?></span>
            <h1 class="register-card__title" id="page-title">Selamat Datang ke Biker Ranger!</h1>
            <p class="register-card__lead">
                Pendaftaran anda berjaya<?= $member !== null ? ', ' . e(strtok((string) $member['name'], ' ') ?: '') : '' ?>!
                Jom tempah slot untuk pit stop yang akan datang. Maklumat anda akan diisi secara automatik.
            </p>

            <div class="register-card__actions">
                <a class="btn btn--primary" href="/pit-stop/daftar">Daftar Event Pit Stop</a>
                <a class="btn btn--ghost" href="/">Laman Utama</a>
            </div>
        </div>
    </div>
</section>
