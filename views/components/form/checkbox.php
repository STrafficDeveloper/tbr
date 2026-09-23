<?php

declare(strict_types=1);

use App\Core\Session;

/**
 * @var string $name
 * @var string $label
 * @var bool|null $required
 * @var bool|null $checked the saved state, used until a failed submit refills the form
 */
$required ??= false;
$checked ??= false;

$id = 'field-' . $name;
$message = error($name);
$refill = Session::getFlash('_old', []);
$isChecked = is_array($refill) && $refill !== [] ? old($name) !== '' : $checked;
?>
<div class="field field--checkbox<?= $message !== null ? ' field--invalid' : '' ?>">
    <input class="field__checkbox" id="<?= e($id) ?>" name="<?= e($name) ?>" type="checkbox" value="1"
           <?= $isChecked ? 'checked' : '' ?>
           <?= $required ? 'required' : '' ?>
           <?= $message !== null ? 'aria-invalid="true" aria-describedby="' . e($id) . '-error"' : '' ?>>
    <label class="field__label" for="<?= e($id) ?>"><?= e($label) ?></label>
<?php if ($message !== null): ?>
    <p class="field__error" id="<?= e($id) ?>-error"><?= e($message) ?></p>
<?php endif; ?>
</div>
