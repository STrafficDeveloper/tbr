<?php

declare(strict_types=1);

/**
 * Image upload showing the current image, with a "remove" tick box when the
 * image is optional.
 *
 * @var string $name
 * @var string $label
 * @var string|null $current upload path of the saved image
 * @var string|null $hint
 * @var bool|null $required
 * @var bool|null $multiple for bulk uploads (name gets [])
 */
$current ??= null;
$hint ??= null;
$required ??= false;
$multiple ??= false;

$id = 'field-' . $name;
$message = error($name);
$maxMb = (int) (\App\Services\ImageUploader::ADMIN_MAX_BYTES / 1024 / 1024);
$hintText = trim(($hint ?? '') . " JPG, PNG atau WebP, maksimum {$maxMb}MB.");
?>
<div class="field<?= $message !== null ? ' field--invalid' : '' ?>">
    <label class="field__label" for="<?= e($id) ?>">
        <?= e($label) ?><?php if ($required && empty($current)): ?> <span class="field__required" aria-hidden="true">*</span><?php endif; ?>
    </label>
<?php if (!empty($current)): ?>
    <img class="field__preview" src="<?= e(uploaded($current)) ?>" alt="Gambar semasa" loading="lazy">
<?php endif; ?>
    <input class="settings__file" id="<?= e($id) ?>" name="<?= e($name) ?><?= $multiple ? '[]' : '' ?>" type="file"
           accept="image/jpeg,image/png,image/webp" <?= $multiple ? 'multiple' : '' ?>
           <?= $required && empty($current) ? 'required' : '' ?>
           aria-describedby="<?= e($id) ?>-hint<?= $message !== null ? ' ' . e($id) . '-error' : '' ?>">
    <p class="field__hint" id="<?= e($id) ?>-hint"><?= e($hintText) ?><?= !empty($current) ? ' Pilih fail baharu untuk menggantikan gambar semasa.' : '' ?></p>
<?php if (!empty($current) && !$required): ?>
    <label class="field__inline-check">
        <input type="checkbox" name="remove_<?= e($name) ?>" value="1"> Buang gambar ini
    </label>
<?php endif; ?>
<?php if ($message !== null): ?>
    <p class="field__error" id="<?= e($id) ?>-error"><?= e($message) ?></p>
<?php endif; ?>
</div>
