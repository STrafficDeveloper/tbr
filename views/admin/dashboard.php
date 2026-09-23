<?php

declare(strict_types=1);

use App\Core\Csrf;

/**
 * @var array{members:int,whatsapp:int,new_this_week:int} $members
 * @var array<string,int> $registrationCounts
 * @var list<array<string,mixed>> $pending
 * @var list<array<string,mixed>> $upcoming
 * @var int $listingCount
 */
$tiles = [
    ['Ahli berdaftar', $members['members'], '/admin/ahli'],
    ['Setuju terima WhatsApp', $members['whatsapp'], '/admin/ahli?tapis=whatsapp'],
    ['Ahli baharu (7 hari)', $members['new_this_week'], '/admin/ahli'],
    ['Pendaftaran menunggu semakan', $registrationCounts['pending'] ?? 0, '/admin/pendaftaran?status=pending'],
    ['Pendaftaran disahkan', $registrationCounts['approved'] ?? 0, '/admin/pendaftaran?status=approved'],
    ['Lokasi Port Rider', $listingCount, '/admin/urus/port-rider'],
];
?>
<ul class="stat-tiles">
<?php foreach ($tiles as [$label, $value, $url]): ?>
    <li>
        <a class="stat-tile" href="<?= e($url) ?>">
            <span class="stat-tile__value"><?= number_format((int) $value) ?></span>
            <span class="stat-tile__label"><?= e($label) ?></span>
        </a>
    </li>
<?php endforeach; ?>
</ul>

<div class="admin-columns">
    <section class="admin-panel" aria-labelledby="pending-title">
        <div class="admin-panel__head">
            <h2 id="pending-title">Menunggu semakan</h2>
            <a href="/admin/pendaftaran?status=pending">Lihat semua</a>
        </div>
<?php if ($pending === []): ?>
        <p class="admin-empty">Tiada pendaftaran menunggu semakan.</p>
<?php else: ?>
        <ul class="admin-list">
<?php foreach ($pending as $row): ?>
            <li class="admin-list__item">
                <div>
                    <strong><?= e($row['name']) ?></strong> &middot; <?= e($row['plate_no']) ?>
                    <p class="admin-muted"><?= e($row['event_title']) ?> &middot; <?= e(formatDate((string) $row['created_at'], true)) ?></p>
                </div>
                <form method="post" action="/admin/pendaftaran/<?= (int) $row['id'] ?>/status" class="admin-inline">
                    <?= Csrf::field() ?>
                    <button class="admin-btn admin-btn--approve" name="status" value="approved">Sahkan</button>
                    <button class="admin-btn admin-btn--reject" name="status" value="rejected">Tolak</button>
                </form>
            </li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
    </section>

    <section class="admin-panel" aria-labelledby="upcoming-title">
        <div class="admin-panel__head">
            <h2 id="upcoming-title">Pit stop akan datang</h2>
            <a href="/admin/urus/pit-stop">Urus</a>
        </div>
<?php if ($upcoming === []): ?>
        <p class="admin-empty">Tiada pit stop akan datang. <a href="/admin/urus/pit-stop/baru">Tambah pit stop</a></p>
<?php else: ?>
        <ul class="admin-list">
<?php foreach ($upcoming as $event): ?>
            <li class="admin-list__item">
                <div>
                    <a href="/admin/urus/pit-stop/<?= (int) $event['id'] ?>"><strong><?= e($event['title']) ?></strong></a>
                    <p class="admin-muted"><?= e(formatDate((string) $event['starts_at'], true)) ?></p>
                </div>
                <a class="admin-muted" href="/admin/pendaftaran?acara=<?= (int) $event['id'] ?>">
                    <?= number_format((int) $event['registrations_count']) ?><?= $event['capacity'] !== null ? ' / ' . number_format((int) $event['capacity']) : '' ?> pendaftaran
                </a>
            </li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
    </section>
</div>
