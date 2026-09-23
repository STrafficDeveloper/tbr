<?php

declare(strict_types=1);

use App\Core\Csrf;
?>
<section class="auth" aria-labelledby="page-title">
    <div class="auth-card">
        <h1 class="auth-card__title" id="page-title">Lupa Kata Laluan</h1>
        <p class="auth-card__lead">Masukkan e-mel akaun anda. Kami akan hantar pautan untuk menetapkan semula kata laluan.</p>

        <form class="form" method="post" action="/lupa-kata-laluan">
            <?= Csrf::field() ?>
            <?= component('form/input', [
                'name' => 'email',
                'label' => 'Alamat E-mel',
                'type' => 'email',
                'required' => true,
                'placeholder' => 'cth: ahmad@gmail.com',
                'autocomplete' => 'email',
            ]) ?>
            <button class="btn btn--primary auth-card__submit" type="submit">Hantar Pautan</button>
        </form>

        <p class="auth-card__footer"><a href="/log-masuk">Kembali ke log masuk</a></p>
    </div>
</section>
