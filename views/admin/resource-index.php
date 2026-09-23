<?php

declare(strict_types=1);

use App\Admin\Resource;
use App\Core\Csrf;
use App\Core\Paginator;

/**
 * @var Resource $resource
 * @var array<string,mixed>|null $parent
 * @var Resource|null $parentResource
 * @var list<array<string,mixed>> $rows
 * @var Paginator $paginator
 * @var string $search
 */
$parentQuery = $parent !== null ? '?parent=' . (int) $parent['id'] : '';
$statusLabels = ['draft' => 'Draf', 'published' => 'Terbit', 'closed' => 'Ditutup'];

$format = static function (array $row, string $column, string $type) use ($statusLabels): string {
    $value = $row[$column] ?? null;

    return match ($type) {
        'date' => e(formatDate(is_string($value) ? $value : null)),
        'datetime' => e(formatDate(is_string($value) ? $value : null, true)),
        'bool' => $value ? '<span class="status status--approved">Ya</span>' : '<span class="admin-muted">—</span>',
        'status' => '<span class="status status--' . ($value === 'published' ? 'approved' : ($value === 'closed' ? 'rejected' : 'pending')) . '">'
            . e($statusLabels[$value] ?? (string) $value) . '</span>',
        'image' => empty($value) ? '<span class="admin-muted">—</span>'
            : '<img class="admin-thumb" src="' . e(uploaded((string) $value)) . '" alt="" loading="lazy">',
        default => e(is_scalar($value) ? (string) $value : ''),
    };
};
?>
<?php if ($parent !== null && $parentResource !== null): ?>
<a class="back-link" href="/admin/urus/<?= e($parentResource->key) ?>/<?= (int) $parent['id'] ?>">
    <?= icon('chevron-left', 'icon icon--sm') ?> <?= e($parentResource->titleOf($parent)) ?>
</a>
<?php endif; ?>

<div class="admin-toolbar">
<?php if ($resource->search !== []): ?>
    <form class="admin-search" method="get" action="/admin/urus/<?= e($resource->key) ?>" role="search">
        <label class="visually-hidden" for="admin-q">Cari</label>
        <input id="admin-q" class="field__control" type="search" name="q" value="<?= e($search) ?>" placeholder="Cari...">
        <button class="admin-btn" type="submit">Cari</button>
    </form>
<?php endif; ?>
    <a class="btn btn--primary" href="/admin/urus/<?= e($resource->key) ?>/baru<?= $parentQuery ?>">+ Tambah <?= e($resource->singular) ?></a>
</div>

<?php if ($resource->bulkImageField !== null && $parent !== null): ?>
<form class="admin-panel admin-bulk" method="post" enctype="multipart/form-data"
      action="/admin/urus/<?= e($resource->key) ?>/muat-naik<?= $parentQuery ?>">
    <?= Csrf::field() ?>
    <?= component('form/image', ['name' => 'photos', 'label' => 'Muat naik beberapa gambar sekaligus', 'multiple' => true]) ?>
    <button class="btn btn--ghost" type="submit">Muat Naik</button>
</form>
<?php endif; ?>

<p class="admin-muted"><?= number_format($paginator->total) ?> rekod</p>

<?php if ($rows === []): ?>
<p class="admin-empty">Belum ada <?= e($resource->singular) ?>. <a href="/admin/urus/<?= e($resource->key) ?>/baru<?= $parentQuery ?>">Tambah yang pertama</a>.</p>
<?php else: ?>
<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
<?php foreach ($resource->columns as $column): ?>
                <th scope="col"><?= e($column[0]) ?></th>
<?php endforeach; ?>
                <th scope="col"><span class="visually-hidden">Tindakan</span></th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($rows as $row): ?>
            <tr>
<?php $first = true; foreach ($resource->columns as $name => $column): ?>
<?php if ($first): $first = false; ?>
                <td><a href="/admin/urus/<?= e($resource->key) ?>/<?= (int) $row['id'] ?><?= $parentQuery ?>"><?= $format($row, $name, $column[1] ?? 'text') ?: e('#' . $row['id']) ?></a></td>
<?php else: ?>
                <td><?= $format($row, $name, $column[1] ?? 'text') ?></td>
<?php endif; ?>
<?php endforeach; ?>
                <td class="admin-table__actions">
                    <a href="/admin/urus/<?= e($resource->key) ?>/<?= (int) $row['id'] ?><?= $parentQuery ?>">Edit</a>
<?php if (($url = $resource->publicUrl($row)) !== null): ?>
                    <a href="<?= e($url) ?>" target="_blank" rel="noopener">Lihat ↗</a>
<?php endif; ?>
                </td>
            </tr>
<?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= component('pagination', ['paginator' => $paginator]) ?>
<?php endif; ?>
