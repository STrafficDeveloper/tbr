<?php

declare(strict_types=1);

/**
 * @var string $name
 * @var string $label
 * @var bool|null $required
 */
$required ??= false;

$id = 'field-' . $name;
$message = error($name);
$checked = old($name) !== '';
?>
<div class="field field--checkbox<?= $message !== null ? ' field--invalid' : '' ?>">
    <input class="field__checkbox" id="<?= e($id) ?>" name="<?= e($name) ?>" type="checkbox" value="1"
           <?= $checked ? 'checked' : '' ?>
           <?= $required ? 'required' : '' ?>
           <?= $message !== null ? 'aria-invalid="true" aria-describedby="' . e($id) . '-error"' : '' ?>>
    <label class="field__label" for="<?= e($id) ?>"><?= e($label) ?></label>
<?php if ($message !== null): ?>
    <p class="field__error" id="<?= e($id) ?>-error"><?= e($message) ?></p>
<?php endif; ?>
</div>
