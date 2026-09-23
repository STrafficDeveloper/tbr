<?php

declare(strict_types=1);

use App\Core\Csrf;
use App\Services\ImageUploader;

/**
 * @var array<string,mixed> $member
 * @var array<string,string> $states
 */
$maxMb = (int) (ImageUploader::MAX_BYTES / 1024 / 1024);
?>
<section class="section" aria-labelledby="page-title">
    <div class="container settings">
        <?= component('section-heading', ['eyebrow' => 'Akaun saya', 'title' => 'Tetapan', 'tag' => 'h1', 'id' => 'page-title']) ?>

        <div class="settings__grid">
            <div class="settings__avatar">
                <img class="settings__avatar-image"
                     src="<?= e(uploaded($member['avatar_path'] ?? null, '/assets/img/avatar-placeholder.svg')) ?>"
                     alt="Gambar profil <?= e((string) $member['name']) ?>" width="200" height="200">

                <form class="settings__avatar-form" method="post" action="/tetapan/gambar" enctype="multipart/form-data">
                    <?= Csrf::field() ?>
                    <label class="field__label" for="field-avatar">Upload Profile</label>
                    <input class="settings__file" id="field-avatar" name="avatar" type="file"
                           accept="image/jpeg,image/png,image/webp" required aria-describedby="avatar-hint">
                    <p class="field__hint" id="avatar-hint">JPG, PNG atau WebP. Maksimum <?= $maxMb ?>MB.</p>
                    <button class="btn btn--ghost" type="submit">Simpan Gambar</button>
                </form>
            </div>

            <form class="form settings__form" method="post" action="/tetapan">
                <?= Csrf::field() ?>
                <div>
                    <h2 class="settings__title">Maklumat Akaun</h2>
                    <p class="settings__lead">Isi maklumat di bawah untuk update</p>
                </div>

                <?= component('form/input', ['name' => 'name', 'label' => 'Nama Penuh', 'required' => true,
                    'value' => (string) $member['name'], 'autocomplete' => 'name']) ?>
                <?= component('form/input', ['name' => 'email', 'label' => 'E-mel', 'type' => 'email', 'required' => true,
                    'value' => (string) $member['email'], 'autocomplete' => 'email']) ?>
                <?= component('form/input', ['name' => 'phone', 'label' => 'Nombor Telefon', 'type' => 'tel', 'required' => true,
                    'value' => displayPhone($member['phone']), 'autocomplete' => 'tel', 'inputmode' => 'tel',
                    'hint' => 'Nombor WhatsApp untuk terima jemputan pit stop.']) ?>
                <?= component('form/select', ['name' => 'state', 'label' => 'Negeri', 'required' => true,
                    'options' => $states, 'value' => (string) $member['state']]) ?>

                <fieldset class="settings__password">
                    <legend class="settings__title settings__title--sm">Tukar Kata Laluan</legend>
                    <?= component('form/input', ['name' => 'new_password', 'label' => 'Kata Laluan Baharu', 'type' => 'password',
                        'autocomplete' => 'new-password', 'hint' => 'Biarkan kosong jika tidak mahu tukar. Sekurang-kurangnya 8 aksara.']) ?>
                    <?= component('form/input', ['name' => 'current_password', 'label' => 'Kata Laluan Semasa', 'type' => 'password',
                        'autocomplete' => 'current-password', 'hint' => 'Diperlukan hanya jika anda menukar e-mel atau kata laluan.']) ?>
                </fieldset>

                <button class="btn btn--primary" type="submit">Simpan</button>
            </form>
        </div>
    </div>
</section>
