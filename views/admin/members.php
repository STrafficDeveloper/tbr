<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Paginator;

/**
 * @var list<array<string,mixed>> $rows
 * @var Paginator $paginator
 * @var string $search
 * @var string|null $filter
 * @var array<string,string> $filters
 */
$states = Config::get('site.states', []);
$optIns = [
    'follows_tbr' => 'Follow TBR',
    'follows_raja_kapcai' => 'Follow Raja Kapcai',
    'whatsapp_opt_in' => 'WhatsApp',
    'contest_opt_in' => 'Peraduan',
];
?>
<form class="admin-filters" method="get" action="/admin/ahli">
    <label>
        <span class="field__label">Tapis</span>
        <select class="field__control" name="tapis">
<?php foreach ($filters as $key => $label): ?>
            <option value="<?= e($key) ?>"<?= (string) $filter === $key ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
        </select>
    </label>
    <label>
        <span class="field__label">Cari</span>
        <input class="field__control" type="search" name="q" value="<?= e($search) ?>" placeholder="Nama, e-mel, telefon...">
    </label>
    <div class="admin-filters__actions">
        <button class="admin-btn" type="submit">Tapis</button>
        <a class="btn btn--ghost" href="/admin/ahli/eksport-whatsapp">Eksport senarai WhatsApp</a>
    </div>
</form>
<p class="admin-muted">
    <?= number_format($paginator->total) ?> ahli. Eksport WhatsApp hanya mengandungi ahli aktif yang bersetuju menerima update melalui WhatsApp (PDPA).
</p>

<?php if ($rows === []): ?>
<p class="admin-empty">Tiada ahli untuk tapisan ini.</p>
<?php else: ?>
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th scope="col">Ahli</th>
                <th scope="col">Negeri</th>
                <th scope="col">Persetujuan</th>
                <th scope="col">Daftar</th>
                <th scope="col">Status</th>
                <th scope="col"><span class="visually-hidden">Tindakan</span></th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($rows as $row): ?>
            <tr>
                <td>
                    <strong><?= e($row['name']) ?></strong>
                    <div class="admin-muted"><?= e($row['email']) ?> &middot;
                        <a href="https://wa.me/<?= e(rawurlencode((string) $row['phone'])) ?>" target="_blank" rel="noopener noreferrer"><?= e(displayPhone($row['phone'])) ?></a>
                    </div>
                </td>
                <td><?= e($states[$row['state']] ?? (string) $row['state']) ?></td>
                <td>
<?php foreach ($optIns as $column => $label): ?>
<?php if ($row[$column]): ?>
                    <span class="admin-tag"><?= e($label) ?></span>
<?php endif; ?>
<?php endforeach; ?>
                </td>
                <td><?= e(formatDate((string) $row['created_at'])) ?></td>
                <td><span class="status status--<?= $row['status'] === 'active' ? 'approved' : 'rejected' ?>"><?= $row['status'] === 'active' ? 'Aktif' : 'Digantung' ?></span></td>
                <td class="admin-table__actions">
                    <form method="post" action="/admin/ahli/<?= (int) $row['id'] ?>/status" class="admin-inline"
                          <?= $row['status'] === 'active' ? 'data-confirm="Gantung akaun ' . e($row['name']) . '? Ahli ini akan dilog keluar serta-merta."' : '' ?>>
                        <?= Csrf::field() ?>
<?php if ($row['status'] === 'active'): ?>
                        <button class="admin-btn admin-btn--reject" name="status" value="suspended">Gantung</button>
<?php else: ?>
                        <button class="admin-btn admin-btn--approve" name="status" value="active">Aktifkan</button>
<?php endif; ?>
                    </form>
                </td>
            </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= component('pagination', ['paginator' => $paginator]) ?>
<?php endif; ?>
