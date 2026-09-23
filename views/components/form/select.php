<?php

declare(strict_types=1);

/**
 * @var string $name
 * @var string $label
 * @var array<string,string> $options value => label
 * @var string|null $placeholder first, unselectable option
 * @var string|null $value
 * @var bool|null $required
 */
$placeholder ??= 'Sila pilih';
$required ??= false;

$id = 'field-' . $name;
$message = error($name);
$selected = old($name, (string) ($value ?? ''));
?>
<div class="field<?= $message !== null ? ' field--invalid' : '' ?>">
    <label class="field__label" for="<?= e($id) ?>">
        <?= e($label) ?><?php if ($required): ?> <span class="field__required" aria-hidden="true">*</span><?php endif; ?>
    </label>
    <div class="field__select">
        <select class="field__control" id="<?= e($id) ?>" name="<?= e($name) ?>"
                <?= $required ? 'required' : '' ?>
                <?= $message !== null ? 'aria-invalid="true" aria-describedby="' . e($id) . '-error"' : '' ?>>
            <option value="" disabled<?= $selected === '' ? ' selected' : '' ?>><?= e($placeholder) ?></option>
<?php foreach ($options as $optionValue => $optionLabel): ?>
            <option value="<?= e((string) $optionValue) ?>"<?= (string) $optionValue === $selected ? ' selected' : '' ?>><?= e($optionLabel) ?></option>
<?php endforeach; ?>
        </select>
        <?= icon('chevron-down', 'icon icon--sm field__select-icon') ?>
    </div>
<?php if ($message !== null): ?>
    <p class="field__error" id="<?= e($id) ?>-error"><?= e($message) ?></p>
<?php endif; ?>
</div>
