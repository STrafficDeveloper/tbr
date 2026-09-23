<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Paginator;

/**
 * @var list<array<string,mixed>> $rows
 * @var Paginator $paginator
 * @var list<array<string,mixed>> $events
 * @var int|null $eventId
 * @var string|null $status
 * @var string $search
 * @var array<string,string> $statuses
 * @var string $exportUrl
 */
$states = Config::get('site.states', []);
$statusClass = ['pending' => 'pending', 'approved' => 'approved', 'rejected' => 'rejected'];
?>
<form class="admin-filters" method="get" action="/admin/pendaftaran">
    <label>
        <span class="field__label">Pit stop</span>
        <select class="field__control" name="acara">
            <option value="">Semua pit stop</option>
<?php foreach ($events as $event): ?>
            <option value="<?= (int) $event['id'] ?>"<?= $eventId === (int) $event['id'] ? ' selected' : '' ?>>
                <?= e($event['title']) ?> (<?= e(formatDate((string) $event['starts_at'])) ?>)
            </option>
<?php endforeach; ?>
        </select>
    </label>
    <label>
        <span class="field__label">Status</span>
        <select class="field__control" name="status">
            <option value="">Semua status</option>
<?php foreach ($statuses as $key => $label): ?>
            <option value="<?= e($key) ?>"<?= $status === $key ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?>
        </select>
    </label>
    <label>
        <span class="field__label">Cari</span>
        <input class="field__control" type="search" name="q" value="<?= e($search) ?>" placeholder="Nama, telefon, plat...">
    </label>
    <div class="admin-filters__actions">
        <button class="admin-btn" type="submit">Tapis</button>
        <a class="btn btn--ghost" href="<?= e($exportUrl) ?>">Eksport CSV</a>
    </div>
</form>

<p class="admin-muted"><?= number_format($paginator->total) ?> pendaftaran</p>

<?php if ($rows === []): ?>
<p class="admin-empty">Tiada pendaftaran untuk tapisan ini.</p>
<?php else: ?>
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th scope="col">Rider</th>
                <th scope="col">Plat</th>
                <th scope="col">Pit stop</th>
                <th scope="col">Didaftar</th>
                <th scope="col">Status</th>
                <th scope="col"><span class="visually-hidden">Tindakan</span></th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($rows as $row): ?>
            <tr>
                <td>
                    <strong><?= e($row['name']) ?></strong>
                    <div class="admin-muted">
                        <a href="https://wa.me/<?= e(rawurlencode((string) $row['phone'])) ?>" target="_blank" rel="noopener noreferrer"><?= e(displayPhone($row['phone'])) ?></a>
                        &middot; <?= e($states[$row['state']] ?? (string) $row['state']) ?>
                    </div>
                </td>
                <td><?= e($row['plate_no']) ?></td>
                <td><?= e($row['event_title']) ?><div class="admin-muted"><?= e(formatDate((string) $row['starts_at'])) ?></div></td>
                <td><?= e(formatDate((string) $row['created_at'], true)) ?></td>
                <td><span class="status status--<?= e($statusClass[$row['status']] ?? 'pending') ?>"><?= e($statuses[$row['status']] ?? $row['status']) ?></span></td>
                <td class="admin-table__actions">
                    <form method="post" action="/admin/pendaftaran/<?= (int) $row['id'] ?>/status" class="admin-inline">
                        <?= Csrf::field() ?>
<?php if ($row['status'] !== 'approved'): ?>
                        <button class="admin-btn admin-btn--approve" name="status" value="approved">Sahkan</button>
<?php endif; ?>
<?php if ($row['status'] !== 'rejected'): ?>
                        <button class="admin-btn admin-btn--reject" name="status" value="rejected">Tolak</button>
<?php endif; ?>
<?php if ($row['status'] !== 'pending'): ?>
                        <button class="admin-btn" name="status" value="pending">Semak semula</button>
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
