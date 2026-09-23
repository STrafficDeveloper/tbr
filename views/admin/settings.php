<?php

declare(strict_types=1);

use App\Core\Csrf;

/**
 * @var array<string,array{0:string,1:string,2:string}> $fields key => [label, type, hint]
 * @var array<string,string|null> $values
 */
?>
<form class="form admin-form" method="post" action="/admin/tetapan">
    <?= Csrf::field() ?>
<?php foreach ($fields as $key => [$label, $type, $hint]): ?>
<?php
    $common = ['name' => $key, 'label' => $label, 'value' => (string) ($values[$key] ?? ''), 'hint' => $hint !== '' ? $hint : null];

    echo match ($type) {
        'textarea' => component('form/textarea', $common + ['rows' => 3]),
        'url' => component('form/input', $common + ['type' => 'url', 'placeholder' => 'https://']),
        default => component('form/input', $common),
    };
?>
<?php endforeach; ?>
    <p class="admin-muted">Pautan media sosial yang dibiarkan kosong tidak akan dipaparkan di footer.</p>
    <div class="admin-form__actions">
        <button class="btn btn--primary" type="submit">Simpan Tetapan</button>
    </div>
</form>
