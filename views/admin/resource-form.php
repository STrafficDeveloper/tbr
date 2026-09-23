<?php

declare(strict_types=1);

use App\Admin\Resource;
use App\Admin\Resources;
use App\Core\Csrf;

/**
 * @var Resource $resource
 * @var array<string,mixed>|null $parent
 * @var array<string,mixed>|null $row  null when creating
 * @var array<string,array{resource:Resource,count:int}> $childCounts
 */
$isEdit = $row !== null;
$parentId = $parent !== null ? (int) $parent['id'] : null;
$parentQuery = $parentId !== null ? '?parent=' . $parentId : '';
$action = '/admin/urus/' . $resource->key . ($isEdit ? '/' . (int) $row['id'] : '') . $parentQuery;
$parentResource = $resource->parentKey !== null ? Resources::get($resource->parentKey) : null;

$valueOf = static function (string $name, mixed $default) use ($row): string {
    $value = $row[$name] ?? $default;

    return $value === null ? '' : (string) $value;
};
?>
<a class="back-link" href="/admin/urus/<?= e($resource->key) ?><?= $parentQuery ?>">
    <?= icon('chevron-left', 'icon icon--sm') ?> <?= e($resource->label) ?><?= $parent !== null && $parentResource !== null ? ': ' . e($parentResource->titleOf($parent)) : '' ?>
</a>

<?php if ($isEdit && ($publicUrl = $resource->publicUrl($row)) !== null): ?>
<p><a href="<?= e($publicUrl) ?>" target="_blank" rel="noopener">Lihat di laman ↗</a></p>
<?php endif; ?>

<?php if ($childCounts !== []): ?>
<nav class="admin-children" aria-label="Kandungan berkaitan">
<?php foreach ($childCounts as $key => ['resource' => $child, 'count' => $count]): ?>
    <a class="admin-child" href="/admin/urus/<?= e($key) ?>?parent=<?= (int) $row['id'] ?>">
        <span class="admin-child__count"><?= number_format($count) ?></span> <?= e($child->label) ?> →
    </a>
<?php endforeach; ?>
</nav>
<?php endif; ?>

<form class="form admin-form" method="post" action="<?= e($action) ?>" enctype="multipart/form-data">
    <?= Csrf::field() ?>
<?php foreach ($resource->fields as $field): ?>
<?php
    $common = ['name' => $field->name, 'label' => $field->label, 'required' => $field->required, 'hint' => $field->hint];
    $value = $valueOf($field->name, $field->default);

    echo match ($field->type) {
        'textarea' => component('form/textarea', $common + ['value' => $value, 'rows' => $field->maxLength <= 500 ? 3 : 6]),
        'select' => component('form/select', $common + [
            'value' => $value,
            'options' => $field->options($parentId),
            'placeholder' => $field->required ? 'Sila pilih' : '— Tiada —',
        ]),
        'checkbox' => component('form/checkbox', ['name' => $field->name, 'label' => $field->label, 'checked' => (bool) ($row[$field->name] ?? $field->default)]),
        'image' => component('form/image', $common + ['current' => $row[$field->name] ?? null]),
        'date' => component('form/input', $common + ['type' => 'date', 'value' => substr($value, 0, 10)]),
        'datetime' => component('form/input', $common + ['type' => 'datetime-local', 'value' => $value === '' ? '' : date('Y-m-d\TH:i', (int) strtotime($value))]),
        'number' => component('form/input', $common + ['type' => 'number', 'value' => $value, 'inputmode' => 'numeric']),
        'decimal' => component('form/input', $common + ['value' => $value, 'inputmode' => 'decimal']),
        'url' => component('form/input', $common + ['type' => 'url', 'value' => $value, 'placeholder' => 'https://']),
        'email' => component('form/input', $common + ['type' => 'email', 'value' => $value]),
        default => component('form/input', $common + ['value' => $value]),
    };
?>
<?php endforeach; ?>

    <div class="admin-form__actions">
        <button class="btn btn--primary" type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah' ?></button>
        <a class="btn btn--ghost" href="/admin/urus/<?= e($resource->key) ?><?= $parentQuery ?>">Batal</a>
    </div>
</form>

<?php if ($isEdit): ?>
<form class="admin-danger" method="post" action="/admin/urus/<?= e($resource->key) ?>/<?= (int) $row['id'] ?>/padam<?= $parentQuery ?>"
      data-confirm="Padam &quot;<?= e($resource->titleOf($row)) ?>&quot;? Tindakan ini tidak boleh dibatalkan.">
    <?= Csrf::field() ?>
    <button class="admin-btn admin-btn--reject" type="submit">Padam <?= e($resource->singular) ?></button>
</form>
<?php endif; ?>
