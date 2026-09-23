<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;

/**
 * @var array<string,mixed>|null $member
 * @var list<array<string,mixed>> $events       open for booking
 * @var string $selected                         slug preselected from ?acara=
 * @var list<array<string,mixed>> $registrations this member's bookings
 * @var array<string,string> $states
 */
$eventOptions = [];
foreach ($events as $event) {
    $eventOptions[(string) $event['slug']] = $event['title'] . ' — ' . formatDate((string) $event['starts_at'])
        . ', ' . $event['location_name'];
}
$statusLabels = ['pending' => 'Dalam semakan', 'approved' => 'Disahkan', 'rejected' => 'Tidak berjaya'];
?>
<section class="section" aria-labelledby="page-title">
    <div class="container register">
        <div class="register-card">
            <h1 class="register-card__title" id="page-title">Daftar Sekarang Untuk Join Event Pitstop Kami</h1>

<?php if ($member === null): ?>
            <p class="register-card__lead">
                Slot pit stop dibuka untuk ahli The Bikers Ranger. Log masuk, atau daftar jadi member secara percuma,
                dan kami akan bawa anda kembali ke sini untuk tempah slot.
            </p>
            <div class="register-card__actions">
                <a class="btn btn--primary" href="/log-masuk">Log Masuk</a>
                <a class="btn btn--ghost" href="/#daftar">Daftar Jadi Member</a>
            </div>

<?php elseif ($eventOptions === []): ?>
            <p class="register-card__lead">Isi maklumat di bawah untuk daftar</p>
            <?= component('empty-state', [
                'message' => 'Tiada pit stop dibuka untuk pendaftaran buat masa ini.',
                'actionUrl' => '/pit-stop',
                'actionLabel' => 'Lihat Jadual',
            ]) ?>

<?php else: ?>
            <p class="register-card__lead">Isi maklumat di bawah untuk daftar</p>

            <form class="form" method="post" action="/pit-stop/daftar">
                <?= Csrf::field() ?>
                <?= component('form/input', [
                    'name' => 'plate',
                    'label' => 'Nombor Plate Biker',
                    'required' => true,
                    'placeholder' => 'cth: WXY 1234',
                    'autocomplete' => 'off',
                    'autocapitalize' => 'characters',
                    'hint' => 'Satu nombor plat untuk setiap pendaftaran.',
                ]) ?>
                <?= component('form/select', [
                    'name' => 'event',
                    'label' => 'Pit Stop Event',
                    'required' => true,
                    'placeholder' => 'Pilih pit stop',
                    'options' => $eventOptions,
                    'value' => array_key_exists($selected, $eventOptions) ? $selected : '',
                ]) ?>
                <?= component('form/select', [
                    'name' => 'state',
                    'label' => 'Negeri Anda Menetap',
                    'placeholder' => 'Pilih negeri',
                    'options' => $states,
                    'value' => (string) ($member['state'] ?? ''),
                ]) ?>

                <p class="register-card__contact">
                    <?= icon('whatsapp', 'icon icon--sm') ?>
                    Jemputan akan dihantar ke WhatsApp <strong><?= e(displayPhone($member['phone'] ?? null)) ?></strong>.
                    <a href="/tetapan">Tukar nombor</a>
                </p>

                <?= component('form/checkbox', [
                    'name' => 'pdpa',
                    'label' => 'I agree to the processing of personal data',
                    'required' => true,
                ]) ?>
                <p class="field__hint"><?= e((string) Config::get('site.privacy_note')) ?></p>

                <button class="btn btn--primary register-card__submit" type="submit">Hantar</button>
            </form>
<?php endif; ?>
        </div>

<?php if ($registrations !== []): ?>
        <section class="my-registrations" id="pendaftaran-saya" aria-labelledby="my-registrations-title">
            <h2 class="my-registrations__title" id="my-registrations-title">Pendaftaran Saya</h2>
            <ul class="my-registrations__list">
<?php foreach ($registrations as $registration): ?>
                <li class="my-registrations__item">
                    <div>
                        <a class="my-registrations__event" href="/pit-stop/<?= e(rawurlencode((string) $registration['slug'])) ?>"><?= e($registration['title']) ?></a>
                        <p class="my-registrations__meta">
                            <?= e(formatDate((string) $registration['starts_at'], true)) ?> &middot; <?= e($registration['plate_no']) ?>
                        </p>
                    </div>
                    <span class="status status--<?= e($registration['status']) ?>"><?= e($statusLabels[$registration['status']] ?? $registration['status']) ?></span>
                </li>
<?php endforeach; ?>
            </ul>
        </section>
<?php endif; ?>
    </div>
</section>
