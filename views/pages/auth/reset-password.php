<?php

declare(strict_types=1);

use App\Core\Csrf;

/**
 * @var string $token
 * @var bool $valid
 */
?>
<section class="auth" aria-labelledby="page-title">
    <div class="auth-card">
<?php if ($valid): ?>
        <h1 class="auth-card__title" id="page-title">Kata Laluan Baharu</h1>
        <p class="auth-card__lead">Pilih kata laluan baharu untuk akaun anda.</p>

        <form class="form" method="post" action="/reset-kata-laluan/<?= e($token) ?>">
            <?= Csrf::field() ?>
            <?= component('form/input', [
                'name' => 'password',
                'label' => 'Kata Laluan Baharu',
                'type' => 'password',
                'required' => true,
                'autocomplete' => 'new-password',
                'hint' => 'Sekurang-kurangnya 8 aksara.',
            ]) ?>
            <button class="btn btn--primary auth-card__submit" type="submit">Simpan Kata Laluan</button>
        </form>
<?php else: ?>
        <h1 class="auth-card__title" id="page-title">Pautan Tidak Sah</h1>
        <p class="auth-card__lead">Pautan ini telah tamat tempoh atau sudah digunakan. Sila minta pautan baharu.</p>
        <a class="btn btn--primary auth-card__submit" href="/lupa-kata-laluan">Minta Pautan Baharu</a>
<?php endif; ?>
    </div>
</section>
