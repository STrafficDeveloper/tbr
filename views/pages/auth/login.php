<?php

declare(strict_types=1);

use App\Core\Csrf;
?>
<section class="auth" aria-labelledby="page-title">
    <div class="auth-card">
        <h1 class="auth-card__title" id="page-title">Log Masuk</h1>
        <p class="auth-card__lead">Isi maklumat di bawah untuk login</p>

        <form class="form" method="post" action="/log-masuk">
            <?= Csrf::field() ?>
            <?= component('form/input', [
                'name' => 'login',
                'label' => 'E-mel atau Nombor Telefon',
                'required' => true,
                'placeholder' => 'cth: ahmad@gmail.com atau 015-558-8645',
                'autocomplete' => 'username',
            ]) ?>
            <?= component('form/input', [
                'name' => 'password',
                'label' => 'Kata Laluan',
                'type' => 'password',
                'required' => true,
                'autocomplete' => 'current-password',
            ]) ?>
            <a class="auth-card__link" href="/lupa-kata-laluan">Lupa kata laluan?</a>
            <button class="btn btn--primary auth-card__submit" type="submit">Masuk</button>
        </form>

        <p class="auth-card__footer">Belum jadi member? <a href="/#daftar">Daftar sekarang</a></p>
    </div>
</section>
