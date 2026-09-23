<?php

declare(strict_types=1);

/**
 * Labelled input with its validation message wired up for screen readers.
 * Values are refilled from the last failed submit, except passwords.
 *
 * @var string $name
 * @var string $label
 * @var string|null $type         text (default), email, tel, password...
 * @var string|null $value        overrides the refill, e.g. the member's saved name
 * @var string|null $placeholder
 * @var string|null $hint
 * @var string|null $autocomplete
 * @var string|null $inputmode
 * @var string|null $autocapitalize e.g. "characters" for number plates
 * @var bool|null $required
 */
$type ??= 'text';
$placeholder ??= null;
$hint ??= null;
$autocomplete ??= null;
$inputmode ??= null;
$autocapitalize ??= null;
$required ??= false;

$id = 'field-' . $name;
$message = error($name);
$fieldValue = $type === 'password' ? '' : old($name, (string) ($value ?? ''));

$describedBy = array_filter([
    $hint !== null ? $id . '-hint' : null,
    $message !== null ? $id . '-error' : null,
]);
?>
<div class="field<?= $message !== null ? ' field--invalid' : '' ?>">
    <label class="field__label" for="<?= e($id) ?>">
        <?= e($label) ?><?php if ($required): ?> <span class="field__required" aria-hidden="true">*</span><?php endif; ?>
    </label>
    <input class="field__control" id="<?= e($id) ?>" name="<?= e($name) ?>" type="<?= e($type) ?>"
           value="<?= e($fieldValue) ?>"
<?php if ($placeholder !== null): ?>
           placeholder="<?= e($placeholder) ?>"
<?php endif; ?>
<?php if ($autocomplete !== null): ?>
           autocomplete="<?= e($autocomplete) ?>"
<?php endif; ?>
<?php if ($inputmode !== null): ?>
           inputmode="<?= e($inputmode) ?>"
<?php endif; ?>
<?php if ($autocapitalize !== null): ?>
           autocapitalize="<?= e($autocapitalize) ?>"
<?php endif; ?>
<?php if ($describedBy !== []): ?>
           aria-describedby="<?= e(implode(' ', $describedBy)) ?>"
<?php endif; ?>
           <?= $required ? 'required' : '' ?>
           <?= $message !== null ? 'aria-invalid="true"' : '' ?>>
<?php if ($hint !== null): ?>
    <p class="field__hint" id="<?= e($id) ?>-hint"><?= e($hint) ?></p>
<?php endif; ?>
<?php if ($message !== null): ?>
    <p class="field__error" id="<?= e($id) ?>-error"><?= e($message) ?></p>
<?php endif; ?>
</div>
