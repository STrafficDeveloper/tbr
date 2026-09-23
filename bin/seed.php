<?php

declare(strict_types=1);

/**
 * Seeds reference content taken from the approved design so every page has
 * something real to render. Safe to re-run: rows are keyed on slug/email.
 *
 * Usage: ADMIN_EMAIL=you@example.com php bin/seed.php
 */

use App\Core\Database;

require dirname(__DIR__) . '/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    exit('Seeds may only be run from the command line.');
}

/** @param array<string,mixed> $row */
function upsert(string $table, array $row, string $uniqueColumn): int
{
    $columns = array_keys($row);
    $placeholders = implode(', ', array_fill(0, count($columns), '?'));
    $assignments = implode(', ', array_map(static fn (string $c): string => "`$c` = VALUES(`$c`)", $columns));

    Database::execute(
        sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            $table,
            implode('`, `', $columns),
            $placeholders,
            $assignments,
        ),
        array_values($row),
    );

    // Not every seeded table has a surrogate key (settings is keyed by its name).
    $found = Database::selectOne(
        sprintf('SELECT * FROM `%s` WHERE `%s` = ? LIMIT 1', $table, $uniqueColumn),
        [$row[$uniqueColumn]],
    );

    return (int) ($found['id'] ?? 0);
}

// --- Admin account -------------------------------------------------------
$adminEmail = getenv('ADMIN_EMAIL') ?: 'admin@hive-asia.com';
$adminPassword = getenv('ADMIN_PASSWORD') ?: bin2hex(random_bytes(9));
$existingAdmin = Database::selectOne('SELECT id FROM users WHERE email = ?', [$adminEmail]);

if ($existingAdmin === null) {
    Database::insert(
        'INSERT INTO users (name, email, password_hash, role, status, email_verified_at)
         VALUES (?, ?, ?, ?, ?, NOW())',
        ['TBR Admin', $adminEmail, password_hash($adminPassword, PASSWORD_DEFAULT), 'admin', 'active'],
    );
    echo "Admin created: {$adminEmail}" . PHP_EOL;
    echo "Admin password: {$adminPassword}  <-- store this now, it is not shown again" . PHP_EOL;
} else {
    echo "Admin already exists: {$adminEmail} (password unchanged)" . PHP_EOL;
}

// --- Home page counters --------------------------------------------------
Database::execute('DELETE FROM site_stats');

foreach ([['30', 'Destinasi'], ['12', 'Bulan'], ['2', 'Pax/Slot'], ['10+', 'Tahun']] as $i => [$value, $label]) {
    Database::insert(
        'INSERT INTO site_stats (value, label, sort_order) VALUES (?, ?, ?)',
        [$value, $label, $i],
    );
}

// --- Gallery categories --------------------------------------------------
foreach (['Bike Paling Hensem', 'Bike Paling Meriah', 'Bike Paling Raya', 'Bike Paling Sempoi'] as $i => $name) {
    upsert('gallery_categories', [
        'name' => $name,
        'slug' => slugify($name),
        'sort_order' => $i,
    ], 'slug');
}

// --- Port Rider directory ------------------------------------------------
// Listings are the ones named in the design's Selangor, Penang and Pahang frames.
$portRiders = [
    ['MOTOKTM (M) SDN BHD', 'bike_shop', 'Petaling Jaya', 'selangor'],
    ['Petronas SS4B Kelana Jaya', 'fuel', 'Petaling Jaya', 'selangor'],
    ['Hodaka Motoworld Sdn Bhd (PJCC)', 'bike_shop', 'Petaling Jaya', 'selangor'],
    ['Restoran Mak Uda Selera Timur', 'food', 'Petaling Jaya', 'selangor'],
    ['Kafe Tok Ali', 'food', 'Petaling Jaya', 'selangor'],
    ['EFORGE Petaling Jaya', 'pitstop', 'Petaling Jaya', 'selangor'],
    ['Shell Petaling Jaya', 'fuel', 'Petaling Jaya', 'selangor'],
    ['Best Bike Motors', 'bike_shop', 'Petaling Jaya', 'selangor'],
    ['Superbike Petronas MotoExpert Workshop And Riding Gear Juru', 'bike_shop', 'Bukit Mertajam', 'pulau-pinang'],
    ['Givi Point Penang', 'bike_shop', 'George Town', 'pulau-pinang'],
    ['DC Biker Touring Motogear Specialist Shop', 'bike_shop', 'Kepala Batas', 'pulau-pinang'],
    ['XIB LAB PENANG', 'bike_shop', 'Simpang Ampat', 'pulau-pinang'],
    ['Honda Impian X - Chang Motor Co., Ltd.', 'bike_shop', 'George Town', 'pulau-pinang'],
    ['Lim Motor Repair', 'bike_shop', 'George Town', 'pulau-pinang'],
    ['Huat Motor', 'bike_shop', 'George Town', 'pulau-pinang'],
    ["WHEELER'S", 'bike_shop', 'George Town', 'pulau-pinang'],
    ['Bike World', 'bike_shop', 'Kuantan', 'pahang'],
    ["Lemang To'ki", 'food', 'Bentong', 'pahang'],
    ['Kopi Kamboh', 'food', 'Janda Baik', 'pahang'],
    ['MGK RACING KUANTAN', 'bike_shop', 'Kuantan', 'pahang'],
    ['Raub Durian Stall 570', 'food', 'Raub', 'pahang'],
    ['Kopi Ladang', 'food', 'Janda Baik', 'pahang'],
    ['Tanarimba', 'pitstop', 'Janda Baik', 'pahang'],
    ['Seaba Seafood', 'food', 'Kuantan', 'pahang'],
];

foreach ($portRiders as [$name, $category, $city, $state]) {
    upsert('port_riders', [
        'name' => $name,
        'slug' => slugify($name),
        'category' => $category,
        'city' => $city,
        'state' => $state,
        'status' => 'published',
    ], 'slug');
}

// --- Pit stop events -----------------------------------------------------
// Demo dates, one stop a month; the admin replaces these with the real tour.
$events = [
    ['Pit Stop Penang', 'pulau-pinang', 'George Town', '2026-10-31 10:00:00'],
    ['Pit Stop Pahang', 'pahang', 'Kuantan', '2026-11-28 10:00:00'],
    ['Pit Stop Perak', 'perak', 'Ipoh', '2026-12-19 10:00:00'],
    ['Pit Stop Melaka', 'melaka', 'Bandar Hilir', '2027-01-23 10:00:00'],
    ['Pit Stop Negeri Sembilan', 'negeri-sembilan', 'Seremban', '2027-02-20 10:00:00'],
    ['Pit Stop Selangor', 'selangor', 'Petaling Jaya', '2027-03-27 10:00:00'],
];

foreach ($events as $i => [$title, $state, $location, $startsAt]) {
    upsert('pitstop_events', [
        'title' => $title,
        'slug' => slugify($title),
        'state' => $state,
        'location_name' => $location,
        'starts_at' => $startsAt,
        'status' => 'published',
        'sort_order' => $i,
    ], 'slug');
}

// --- Peraduan (contests) -------------------------------------------------
$tagline = 'Kasi nampak jentera kesayangan korang! Tak payah nak lumba, '
    . 'tak payah nak pulas trotel sampai putus...';

$contests = [
    ['SNAP-JE-MENANG 01', '2026-10-01', '2026-10-31', '2026-11-10'],
    ['SNAP-JE-MENANG 02', '2026-11-01', '2026-11-30', '2026-12-10'],
    ['SNAP-JE-MENANG 03', '2026-12-01', '2026-12-31', '2027-01-10'],
];

$prizes = [
    ['Hadiah Utama', 'Barangan Tunggangan Premium'],
    ['Tempat Ke-2', 'Aksesori Motosikal'],
    ['Tempat Ke-3', 'Barangan Komuniti TBR'],
];

foreach ($contests as [$title, $startsOn, $endsOn, $announceOn]) {
    $contestId = upsert('contests', [
        'title' => $title,
        'slug' => slugify($title),
        'tagline' => $tagline,
        'starts_on' => $startsOn,
        'ends_on' => $endsOn,
        'announce_on' => $announceOn,
        'status' => 'published',
    ], 'slug');

    Database::execute('DELETE FROM contest_prizes WHERE contest_id = ?', [$contestId]);

    foreach ($prizes as $i => [$rankLabel, $prizeName]) {
        Database::insert(
            'INSERT INTO contest_prizes (contest_id, rank_label, prize_name, sort_order) VALUES (?, ?, ?, ?)',
            [$contestId, $rankLabel, $prizeName, $i],
        );
    }
}

// --- Panas Atas Jalan videos ---------------------------------------------
$videos = [
    'Abam Penyelamat Kucing',
    'Bijaksana Bijaksini',
    'Ekzos Selsema',
    'Kejung Member Belakang',
    'Tibai Pakar Hammer',
];

foreach ($videos as $i => $title) {
    upsert('videos', [
        'title' => $title,
        'slug' => slugify($title),
        'section' => 'panas_atas_jalan',
        'provider' => 'youtube',
        'sort_order' => $i,
        'status' => 'published',
        'published_at' => date('Y-m-d H:i:s'),
    ], 'slug');
}

// --- Hall of Fame: Wazi Abdul Hamid --------------------------------------
$hofId = upsert('hof_profiles', [
    'name' => 'Wazi Abdul Hamid',
    'slug' => 'wazi-abdul-hamid',
    'handle' => '@waziabdulhamid',
    'headline' => 'Raja Kapcai / Raja Cub Prix',
    'summary' => 'Wazi Abdul Hamid adalah legenda lumba motosikal Malaysia yang dikenali sebagai '
        . '"Raja Cub Prix" selepas menjuarai kategori Pakar pada tahun 1996.',
    'is_hero_of_month' => 1,
    'featured_month' => date('Y-m-01'),
    'status' => 'published',
], 'slug');

$sections = [
    ['Biodata', 'Nama: Wazi bin Abdul Hamid. Dikenali: "Raja Kapcai" / "Raja Cub Prix". '
        . 'Asal: Ipoh, Perak. Negeri Kelahiran: Pulau Pinang. Tahun Kelahiran: 1971.'],
    ['Pendidikan', 'Pendidikan Menengah: Kota Bharu. Pendidikan Tinggi: Doktor Pentadbiran '
        . 'Perniagaan (DBA) melalui laluan APEL.Q di City University Malaysia.'],
    ['Permulaan Kerjaya', 'Beliau mula aktif dalam dunia perlumbaan sekitar 1989, bermula sebagai '
        . 'kru perlumbaan sebelum akhirnya menjadi pelumba profesional pada tahun 1992.'],
    ['Pencapaian Antarabangsa', 'Meraih tempat kedua keseluruhan dalam FIM Asia Road Racing '
        . 'Championship pada tahun 2001 dan 2003.'],
    ['Persaraan', 'Beliau menamatkan kerjaya sebagai pelumba sekitar 2006 hingga 2007.'],
    ['Pasca Bersara', 'Aktif dalam latihan keselamatan motosikal, pendidikan penunggang dan program '
        . 'keselamatan jalan raya, termasuk program yang melibatkan JPJ.'],
    ['MotoWazi', 'Membangunkan MotoWazi sebagai satu inisiatif yang memberi fokus kepada latihan '
        . 'penunggang dan keselamatan motosikal. Presiden MASRA (Motorcycle Advanced & Safety Riding Academy).'],
];

Database::execute('DELETE FROM hof_sections WHERE hof_profile_id = ?', [$hofId]);

foreach ($sections as $i => [$heading, $body]) {
    Database::insert(
        'INSERT INTO hof_sections (hof_profile_id, heading, body, sort_order) VALUES (?, ?, ?, ?)',
        [$hofId, $heading, $body, $i],
    );
}

// --- Promo banners shown above the footer --------------------------------
Database::execute("DELETE FROM banners WHERE placement = 'global'");

$banners = [
    [
        'Moh Lepak Sama Geng The Bikers Ranger',
        'Satu jalan, satu minat. Jom lepak, sembang dan kongsi cerita tentang perjalanan engkorang. '
            . 'Dari pengalaman atas jalan sampailah kisah di sebalik setiap ride, mesti ada cerita nak borak!',
        '/#daftar',
        'Daftar Sekarang',
    ],
    [
        'Tahniah Kepada Semua 100 Pemenang',
        'Senarai penuh pemenang peraduan SNAP-JE-MENANG kini diumumkan. Semak nama anda sekarang!',
        '/aktiviti/pemenang',
        'Ketahui Lebih Lanjut',
    ],
];

foreach ($banners as $i => [$title, $body, $link, $cta]) {
    Database::insert(
        'INSERT INTO banners (title, body, placement, link_url, cta_label, sort_order, status)
         VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$title, $body, 'global', $link, $cta, $i, 'published'],
    );
}

// --- Editable site copy --------------------------------------------------
$settings = [
    'home_hero_title' => 'Konvoi biker seluruh Malaysia. Dari komuniti jadi realiti.',
    'home_gathering_title' => 'Bukan race. Bukan rally.',
    'home_gathering_highlight' => 'Ini gathering.',
    'pitstop_intro' => 'Satu pitstop, banyak cerita, jom eratkan hubungan bersama!',
    'community_intro' => 'Satu jalan, satu minat. Jom lepak, sembang dan kongsi cerita tentang '
        . 'perjalanan engkorang. Dari pengalaman atas jalan sampailah kisah di sebalik setiap ride, '
        . 'mesti ada cerita nak borak!',
];

foreach ($settings as $key => $value) {
    upsert('settings', ['setting_key' => $key, 'setting_value' => $value], 'setting_key');
}

echo 'Seeding complete.' . PHP_EOL;
