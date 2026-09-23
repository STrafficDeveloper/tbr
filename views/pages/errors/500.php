<?php

declare(strict_types=1);

/**
 * Rendered by ErrorHandler with no layout and no database access.
 *
 * @var string|null $detail error text, only when APP_DEBUG is on
 */
?>
<!DOCTYPE html>
<html lang="ms-MY">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Ralat Pelayan | The Bikers Ranger</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <main class="container error-page">
        <h1>Alamak, ada masalah teknikal</h1>
        <p>Maaf, halaman ini tidak dapat dipaparkan sekarang. Sila cuba lagi sebentar lagi.</p>
        <a class="btn btn--primary" href="/">Kembali ke halaman utama</a>
<?php if ($detail !== null): ?>
        <pre class="error-page__debug"><?= htmlspecialchars($detail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></pre>
<?php endif; ?>
    </main>
</body>
</html>
