<?php

declare(strict_types=1);

/**
 * @var string $message
 * @var string|null $actionUrl
 * @var string|null $actionLabel
 */
$actionUrl ??= null;
$actionLabel ??= null;
?>
<div class="empty-state">
    <p><?= e($message) ?></p>
<?php if ($actionUrl !== null && $actionLabel !== null): ?>
    <a class="btn btn--ghost" href="<?= e($actionUrl) ?>"><?= e($actionLabel) ?></a>
<?php endif; ?>
</div>
