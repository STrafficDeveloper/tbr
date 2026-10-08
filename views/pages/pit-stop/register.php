<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;

/**
 * @var array<string,mixed>|null $member        signed in: their details pre-fill the form
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

<?php if ($eventOptions === []): ?>
            <p class="register-card__lead">Isi maklumat di bawah untuk daftar</p>
            <?= component('empty-state', [
                'message' => 'Tiada pit stop dibuka untuk pendaftaran buat masa ini.',
                'actionUrl' => '/pit-stop',
                'actionLabel' => 'Lihat Jadual',
            ]) ?>

<?php else: ?>
            <p class="register-card__lead">Isi maklumat di bawah untuk daftar</p>
<?php if ($member !== null): ?>
            <p class="register-card__prefill">
                <?= icon('check', 'icon icon--sm') ?>
                Maklumat anda telah diisi daripada akaun ahli. Semak dan lengkapkan yang selebihnya.
            </p>
<?php endif; ?>

            <form class="form" method="post" action="/pit-stop/daftar">
                <?= Csrf::field() ?>
                <?= component('form/input', [
                    'name' => 'name',
                    'label' => 'Nama Penuh',
                    'required' => true,
                    'placeholder' => 'cth: Ahmad bin Abdullah',
                    'autocomplete' => 'name',
                    'value' => (string) ($member['name'] ?? ''),
                ]) ?>
                <?= component('form/input', [
                    'name' => 'phone',
                    'label' => 'Nombor Telefon',
                    'type' => 'tel',
                    'required' => true,
                    'placeholder' => 'cth: 015-558-8645',
                    'autocomplete' => 'tel',
                    'inputmode' => 'tel',
                    'value' => isset($member['phone']) ? displayPhone((string) $member['phone']) : '',
                ]) ?>
                <?= component('form/input', [
                    'name' => 'email',
                    'label' => 'Alamat E-mel',
                    'type' => 'email',
                    'required' => true,
                    'placeholder' => 'cth: abu@example.com',
                    'autocomplete' => 'email',
                    'hint' => 'Pengesahan pendaftaran dihantar ke e-mel ini.',
                    'value' => (string) ($member['email'] ?? ''),
                ]) ?>
                <?= component('form/input', [
                    'name' => 'plate',
                    'label' => 'Nombor Plat Motosikal',
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

                <div class="hp" aria-hidden="true">
                    <label for="field-website">Website</label>
                    <input id="field-website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>

                <?= component('form/checkbox', [
                    'name' => 'pdpa',
                    'label' => 'Saya bersetuju dengan pemprosesan data peribadi saya',
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
