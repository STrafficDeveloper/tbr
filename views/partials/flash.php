<?php

declare(strict_types=1);

use App\Core\Session;

$status = Session::getFlash('_status');

if (!is_array($status) || empty($status['message'])) {
    return;
}

$type = in_array($status['type'] ?? '', ['success', 'error', 'info'], true) ? $status['type'] : 'info';
?>
<div class="container">
    <div class="flash flash--<?= e($type) ?>" role="<?= $type === 'error' ? 'alert' : 'status' ?>">
        <?= e((string) $status['message']) ?>
    </div>
</div>
