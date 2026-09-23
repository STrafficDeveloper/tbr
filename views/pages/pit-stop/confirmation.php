<?php

declare(strict_types=1);

use App\Core\Config;

/** @var array<string,mixed> $registration */
$states = Config::get('site.states', []);
?>
<section class="section" aria-labelledby="page-title">
    <div class="container register">
        <div class="register-card register-card--done">
            <span class="register-card__tick" aria-hidden="true"><?= icon('check') ?></span>
            <h1 class="register-card__title" id="page-title">Pendaftaran Diterima!</h1>
            <p class="register-card__lead">Pendaftaran anda akan disemak. Kami akan hubungi anda tidak lama lagi.</p>

            <dl class="summary">
                <div><dt>Pit stop</dt><dd><?= e($registration['title']) ?></dd></div>
                <div><dt>Tarikh</dt><dd><?= e(formatDate((string) $registration['starts_at'], true)) ?></dd></div>
                <div><dt>Lokasi</dt><dd><?= e($registration['location_name'] . ', ' . ($states[$registration['state']] ?? $registration['state'])) ?></dd></div>
                <div><dt>Nombor plat</dt><dd><?= e($registration['plate_no']) ?></dd></div>
            </dl>

            <div class="register-card__actions">
                <a class="btn btn--primary" href="/">Kembali ke halaman utama</a>
                <a class="btn btn--ghost" href="/pit-stop/daftar#pendaftaran-saya">Pendaftaran Saya</a>
            </div>
        </div>
    </div>
</section>
