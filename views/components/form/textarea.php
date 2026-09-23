<?php

declare(strict_types=1);

/**
 * @var string $name
 * @var string $label
 * @var string|null $value
 * @var string|null $hint
 * @var bool|null $required
 * @var int|null $rows
 */
$hint ??= null;
$required ??= false;
$rows ??= 5;

$id = 'field-' . $name;
$message = error($name);
$describedBy = array_filter([$hint !== null ? $id . '-hint' : null, $message !== null ? $id . '-error' : null]);
?>
<div class="field<?= $message !== null ? ' field--invalid' : '' ?>">
    <label class="field__label" for="<?= e($id) ?>">
        <?= e($label) ?><?php if ($required): ?> <span class="field__required" aria-hidden="true">*</span><?php endif; ?>
    </label>
    <textarea class="field__control field__control--textarea" id="<?= e($id) ?>" name="<?= e($name) ?>" rows="<?= (int) $rows ?>"
              <?= $required ? 'required' : '' ?>
              <?= $describedBy !== [] ? 'aria-describedby="' . e(implode(' ', $describedBy)) . '"' : '' ?>
              <?= $message !== null ? 'aria-invalid="true"' : '' ?>><?= e(old($name, (string) ($value ?? ''))) ?></textarea>
<?php if ($hint !== null): ?>
    <p class="field__hint" id="<?= e($id) ?>-hint"><?= e($hint) ?></p>
<?php endif; ?>
<?php if ($message !== null): ?>
    <p class="field__error" id="<?= e($id) ?>-error"><?= e($message) ?></p>
<?php endif; ?>
</div>
