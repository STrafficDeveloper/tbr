<?php

declare(strict_types=1);

/**
 * Applies any .sql file in database/migrations that has not run yet.
 * Usage: php bin/migrate.php [--status]
 */

use App\Core\Database;

require dirname(__DIR__) . '/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    exit('Migrations may only be run from the command line.');
}

Database::execute(
    'CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_migrations_filename (filename)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
);

$applied = array_column(Database::select('SELECT filename FROM migrations'), 'filename');
$files = glob(BASE_PATH . '/database/migrations/*.sql') ?: [];
sort($files);

if (in_array('--status', $argv, true)) {
    foreach ($files as $file) {
        $name = basename($file);
        echo (in_array($name, $applied, true) ? '  applied  ' : '  pending  ') . $name . PHP_EOL;
    }
    exit(0);
}

$pending = array_filter($files, static fn (string $f): bool => !in_array(basename($f), $applied, true));

if ($pending === []) {
    exit('Nothing to migrate.' . PHP_EOL);
}

foreach ($pending as $file) {
    $name = basename($file);
    echo 'Migrating ' . $name . ' ... ';

    $sql = (string) file_get_contents($file);

    foreach (explode(";\n", $sql) as $statement) {
        // Drop comment lines first: a statement that merely opens with one is
        // still a statement, and skipping it would silently lose a table.
        $lines = array_filter(
            explode("\n", $statement),
            static fn (string $line): bool => !str_starts_with(trim($line), '--'),
        );

        $statement = trim(implode("\n", $lines), "; \n\r\t");

        if ($statement === '') {
            continue;
        }

        Database::execute($statement);
    }

    Database::insert('INSERT INTO migrations (filename) VALUES (?)', [$name]);
    echo 'done' . PHP_EOL;
}

echo 'Migrations complete.' . PHP_EOL;
