<?php

declare(strict_types=1);

use App\Controllers\PitStopController;
use App\Core\Auth;
use App\Core\Config;

/**
 * Shown once, straight after a booking. No QR code: attendance is ticked off
 * by the crew from the admin checklist using the registration number.
 *
 * @var array<string,mixed> $registration
 * @var array{google:string,waze:string} $directions
 */
$states = Config::get('site.states', []);
$location = implode(', ', array_filter([
    (string) $registration['location_name'],
    (string) ($registration['address'] ?? ''),
    (string) ($states[$registration['state']] ?? $registration['state']),
]));
?>
<section class="section" aria-labelledby="page-title">
    <div class="container register">
        <div class="register-card register-card--done">
            <span class="register-card__tick" aria-hidden="true"><?= icon('check') ?></span>
            <h1 class="register-card__title" id="page-title">Pendaftaran Pitstop Event Berjaya!</h1>
            <p class="register-card__lead">
                Pengesahan telah dihantar ke <strong><?= e((string) $registration['email']) ?></strong>.
                Tunjukkan nombor pendaftaran ini semasa tiba.
            </p>

            <dl class="summary">
                <div><dt>Nombor pendaftaran</dt><dd class="summary__ref"><?= e(PitStopController::reference((int) $registration['id'])) ?></dd></div>
                <div><dt>Nama event</dt><dd><?= e($registration['title']) ?></dd></div>
                <div><dt>Tarikh</dt><dd><?= e(formatDate((string) $registration['starts_at'])) ?></dd></div>
                <div><dt>Masa</dt><dd><?= e(formatTime((string) $registration['starts_at'], $registration['ends_at'] ?? null)) ?></dd></div>
                <div><dt>Lokasi</dt><dd><?= e($location) ?></dd></div>
                <div><dt>Nama</dt><dd><?= e($registration['name']) ?></dd></div>
                <div><dt>Nombor plat</dt><dd><?= e($registration['plate_no']) ?></dd></div>
            </dl>

            <div class="directions">
                <p class="directions__label">Dapatkan arah sekarang</p>
                <a class="btn btn--ghost" href="<?= e($directions['google']) ?>" target="_blank" rel="noopener noreferrer">Google Maps</a>
                <a class="btn btn--ghost" href="<?= e($directions['waze']) ?>" target="_blank" rel="noopener noreferrer">Waze</a>
            </div>

            <div class="register-card__actions">
                <a class="btn btn--primary" href="/">Laman Utama</a>
<?php if (Auth::check()): ?>
                <a class="btn btn--ghost" href="/pit-stop/daftar#pendaftaran-saya">Pendaftaran Saya</a>
<?php else: ?>
                <a class="btn btn--ghost" href="/pit-stop/daftar">Daftar Motor Lain</a>
<?php endif; ?>
            </div>
        </div>
    </div>
</section>
