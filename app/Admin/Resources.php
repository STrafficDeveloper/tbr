<?php

declare(strict_types=1);

namespace App\Admin;

use App\Core\Config;
use App\Core\Database;

/**
 * Every content type the admin panel manages. Adding a new one is a matter of
 * describing it here; the generic controller, forms and lists follow.
 */
final class Resources
{
    /** @var array<string,Resource>|null */
    private static ?array $all = null;

    /** @return array<string,Resource> */
    public static function all(): array
    {
        return self::$all ??= self::define();
    }

    public static function get(string $key): ?Resource
    {
        return self::all()[$key] ?? null;
    }

    /** @return array<string,Resource> */
    private static function define(): array
    {
        $publish = ['draft' => 'Draf', 'published' => 'Terbit'];
        $states = Config::get('site.states', []);
        $sort = new Field('sort_order', 'Susunan', 'number', default: 0, hint: 'Nombor kecil dipaparkan dahulu.');

        $resources = [
            new Resource(
                key: 'pit-stop',
                label: 'Pit Stop',
                singular: 'pit stop',
                table: 'pitstop_events',
                fields: [
                    new Field('title', 'Tajuk', required: true, maxLength: 160),
                    new Field('slug', 'Slug URL', 'slug', slugFrom: 'title', maxLength: 180, hint: 'Biarkan kosong untuk dijana daripada tajuk.'),
                    new Field('starts_at', 'Tarikh & masa mula', 'datetime', required: true),
                    new Field('ends_at', 'Tarikh & masa tamat', 'datetime'),
                    new Field('state', 'Negeri', 'select', required: true, options: $states),
                    new Field('location_name', 'Nama lokasi', required: true, maxLength: 180, hint: 'cth: George Town'),
                    new Field('address', 'Alamat'),
                    new Field('maps_url', 'Pautan Google Maps', 'url'),
                    new Field('latitude', 'Latitud', 'decimal'),
                    new Field('longitude', 'Longitud', 'decimal'),
                    new Field('capacity', 'Kapasiti slot', 'number', hint: 'Biarkan kosong untuk tiada had.'),
                    new Field('description', 'Penerangan', 'textarea'),
                    new Field('banner_image', 'Gambar banner', 'image', imageWidth: 1600),
                    new Field('status', 'Status', 'select', required: true, options: $publish + ['closed' => 'Ditutup'], default: 'draft'),
                    $sort,
                ],
                columns: ['title' => ['Tajuk'], 'starts_at' => ['Tarikh', 'datetime'], 'state' => ['Negeri'], 'status' => ['Status', 'status']],
                orderBy: 'starts_at DESC',
                search: ['title', 'location_name'],
                publicPath: '/pit-stop/{slug}',
                deleteGuard: static function (array $row): ?string {
                    $taken = Database::selectOne('SELECT COUNT(*) AS n FROM pitstop_registrations WHERE event_id = ?', [$row['id']]);

                    return (int) $taken['n'] > 0
                        ? 'Pit stop ini sudah ada pendaftaran. Tukar status kepada "Ditutup" supaya rekod pendaftaran kekal.'
                        : null;
                },
            ),

            new Resource(
                key: 'port-rider',
                label: 'Port Rider',
                singular: 'lokasi',
                table: 'port_riders',
                fields: [
                    new Field('name', 'Nama', required: true, maxLength: 180),
                    new Field('slug', 'Slug URL', 'slug', slugFrom: 'name', maxLength: 200, hint: 'Biarkan kosong untuk dijana daripada nama.'),
                    new Field('category', 'Kategori', 'select', required: true, default: 'bike_shop', options: [
                        'bike_shop' => 'Bike shop / bengkel',
                        'pitstop' => 'Pit stop / tempat lepak',
                        'food' => 'Makan',
                        'fuel' => 'Stesen minyak',
                        'other' => 'Lain-lain',
                    ]),
                    new Field('address', 'Alamat'),
                    new Field('city', 'Bandar', maxLength: 80),
                    new Field('state', 'Negeri', 'select', required: true, options: $states),
                    new Field('postcode', 'Poskod', maxLength: 10),
                    new Field('phone', 'Telefon', maxLength: 30),
                    new Field('maps_url', 'Pautan Google Maps', 'url', hint: 'Jika kosong, butang lokasi akan mencari nama & bandar di Google Maps.'),
                    new Field('latitude', 'Latitud', 'decimal'),
                    new Field('longitude', 'Longitud', 'decimal'),
                    new Field('image', 'Gambar', 'image', imageWidth: 1200),
                    new Field('description', 'Penerangan', 'textarea'),
                    new Field('status', 'Status', 'select', required: true, options: $publish, default: 'draft'),
                ],
                columns: ['name' => ['Nama'], 'city' => ['Bandar'], 'state' => ['Negeri'], 'likes_count' => ['Suka'], 'status' => ['Status', 'status']],
                orderBy: 'name ASC',
                search: ['name', 'city'],
            ),

            new Resource(
                key: 'galeri',
                label: 'Galeri',
                singular: 'album',
                table: 'galleries',
                fields: [
                    new Field('title', 'Tajuk album', required: true, maxLength: 180),
                    new Field('slug', 'Slug URL', 'slug', slugFrom: 'title', maxLength: 200, hint: 'Biarkan kosong untuk dijana daripada tajuk.'),
                    new Field('description', 'Penerangan', 'textarea', maxLength: 500),
                    new Field('cover_image', 'Gambar muka depan', 'image', imageWidth: 1200, hint: 'Jika kosong, gambar pertama album digunakan.'),
                    new Field('published_at', 'Tarikh terbit', 'datetime'),
                    new Field('status', 'Status', 'select', required: true, options: $publish, default: 'draft'),
                    $sort,
                ],
                columns: ['title' => ['Tajuk'], 'published_at' => ['Terbit', 'datetime'], 'status' => ['Status', 'status']],
                orderBy: 'sort_order ASC, id DESC',
                search: ['title'],
                children: ['gambar-galeri'],
                publicPath: '/aktiviti/galeri/{slug}',
            ),

            new Resource(
                key: 'gambar-galeri',
                label: 'Gambar Album',
                singular: 'gambar',
                table: 'gallery_images',
                fields: [
                    new Field('path', 'Gambar', 'image', required: true, imageWidth: 1600),
                    new Field('alt_text', 'Teks alternatif', hint: 'Terangkan gambar untuk pembaca skrin dan Google.'),
                    new Field('caption', 'Kapsyen', maxLength: 500),
                    $sort,
                ],
                columns: ['path' => ['Gambar', 'image'], 'caption' => ['Kapsyen'], 'sort_order' => ['Susunan']],
                orderBy: 'sort_order ASC, id ASC',
                parentKey: 'galeri',
                foreignKey: 'gallery_id',
                bulkImageField: 'path',
                titleColumn: 'caption',
            ),

            new Resource(
                key: 'peraduan',
                label: 'Peraduan',
                singular: 'peraduan',
                table: 'contests',
                fields: [
                    new Field('title', 'Tajuk', required: true, maxLength: 180),
                    new Field('slug', 'Slug URL', 'slug', slugFrom: 'title', maxLength: 200, hint: 'Biarkan kosong untuk dijana daripada tajuk.'),
                    new Field('tagline', 'Ayat ringkas', 'textarea', maxLength: 500),
                    new Field('starts_on', 'Tarikh mula', 'date', required: true),
                    new Field('ends_on', 'Tarikh tamat', 'date', required: true),
                    new Field('announce_on', 'Tarikh umum pemenang', 'date', hint: 'Senarai pemenang hanya dipaparkan mulai tarikh ini.'),
                    new Field('eligibility', 'Kelayakan penyertaan', 'textarea'),
                    new Field('rules', 'Cara sertai', 'textarea', hint: 'Satu langkah bagi setiap baris.'),
                    new Field('whatsapp_number', 'Nombor WhatsApp', maxLength: 20, hint: 'Jika kosong, nombor WhatsApp utama laman digunakan.'),
                    new Field('whatsapp_message', 'Mesej WhatsApp', maxLength: 500),
                    new Field('cover_image', 'Gambar', 'image', imageWidth: 1200),
                    new Field('status', 'Status', 'select', required: true, options: $publish, default: 'draft'),
                ],
                columns: ['title' => ['Tajuk'], 'starts_on' => ['Mula', 'date'], 'ends_on' => ['Tamat', 'date'], 'status' => ['Status', 'status']],
                orderBy: 'starts_on DESC',
                search: ['title'],
                children: ['hadiah', 'pemenang'],
                publicPath: '/aktiviti/peraduan/{slug}',
            ),

            new Resource(
                key: 'hadiah',
                label: 'Hadiah',
                singular: 'hadiah',
                table: 'contest_prizes',
                fields: [
                    new Field('rank_label', 'Kedudukan', required: true, maxLength: 80, hint: 'cth: Hadiah Utama, Tempat Ke-2'),
                    new Field('prize_name', 'Hadiah', required: true, maxLength: 180),
                    new Field('prize_value', 'Nilai', maxLength: 80, hint: 'cth: RM500'),
                    $sort,
                ],
                columns: ['rank_label' => ['Kedudukan'], 'prize_name' => ['Hadiah'], 'prize_value' => ['Nilai']],
                orderBy: 'sort_order ASC, id ASC',
                parentKey: 'peraduan',
                foreignKey: 'contest_id',
                titleColumn: 'rank_label',
            ),

            new Resource(
                key: 'pemenang',
                label: 'Pemenang',
                singular: 'pemenang',
                table: 'contest_winners',
                fields: [
                    new Field('name', 'Nama', required: true, maxLength: 120),
                    new Field('prize_id', 'Hadiah', 'select', options: static fn (?int $contestId): array => array_column(
                        Database::select("SELECT id, CONCAT(rank_label, ' — ', prize_name) AS label FROM contest_prizes WHERE contest_id = ? ORDER BY sort_order", [(int) $contestId]),
                        'label',
                        'id',
                    ), hint: 'Kosongkan untuk pemenang hadiah saguhati.'),
                    new Field('position', 'Kedudukan (1, 2, 3)', 'number'),
                    new Field('is_consolation', 'Hadiah saguhati', 'checkbox'),
                    new Field('bike', 'Motosikal', maxLength: 120),
                    new Field('plate_masked', 'Nombor plat (disembunyikan)', maxLength: 20, hint: 'Sembunyikan sebahagian, cth: V** **21'),
                    new Field('photo', 'Gambar', 'image', imageWidth: 600),
                    $sort,
                ],
                columns: ['name' => ['Nama'], 'position' => ['Kedudukan'], 'is_consolation' => ['Saguhati', 'bool']],
                orderBy: 'is_consolation ASC, position ASC, sort_order ASC, id ASC',
                parentKey: 'peraduan',
                foreignKey: 'contest_id',
            ),

            new Resource(
                key: 'video',
                label: 'Video',
                singular: 'video',
                table: 'videos',
                fields: [
                    new Field('title', 'Tajuk', required: true, maxLength: 180),
                    new Field('slug', 'Slug', 'slug', slugFrom: 'title', maxLength: 200, hint: 'Biarkan kosong untuk dijana daripada tajuk.'),
                    new Field('section', 'Bahagian', 'select', required: true, default: 'panas_atas_jalan', options: [
                        'panas_atas_jalan' => 'Panas Atas Jalan',
                        'hall_of_fame' => 'Hall of Fame (Content)',
                    ]),
                    new Field('hof_profile_id', 'Profil Hall of Fame', 'select', hint: 'Hanya untuk video Hall of Fame.', options: static fn (): array => array_column(
                        Database::select('SELECT id, name FROM hof_profiles ORDER BY name'),
                        'name',
                        'id',
                    )),
                    new Field('provider', 'Sumber', 'select', required: true, default: 'youtube', options: ['youtube' => 'YouTube', 'tiktok' => 'TikTok', 'file' => 'Pautan lain']),
                    new Field('video_id', 'Pautan atau ID YouTube', 'youtube', hint: 'Tampal pautan YouTube penuh; ID akan diambil secara automatik.'),
                    new Field('video_url', 'Pautan video (bukan YouTube)', 'url'),
                    new Field('thumbnail', 'Gambar kecil', 'image', imageWidth: 960, hint: 'Pilihan untuk YouTube: gambar kecil YouTube digunakan jika kosong.'),
                    new Field('description', 'Penerangan', 'textarea', maxLength: 500),
                    new Field('duration_seconds', 'Tempoh (saat)', 'number'),
                    new Field('published_at', 'Tarikh terbit', 'datetime'),
                    new Field('status', 'Status', 'select', required: true, options: $publish, default: 'draft'),
                    $sort,
                ],
                columns: ['title' => ['Tajuk'], 'section' => ['Bahagian'], 'published_at' => ['Terbit', 'datetime'], 'status' => ['Status', 'status']],
                orderBy: 'published_at DESC, id DESC',
                search: ['title'],
            ),

            new Resource(
                key: 'hall-of-fame',
                label: 'Hall of Fame',
                singular: 'profil',
                table: 'hof_profiles',
                fields: [
                    new Field('name', 'Nama', required: true, maxLength: 160),
                    new Field('slug', 'Slug URL', 'slug', slugFrom: 'name', maxLength: 180, hint: 'Biarkan kosong untuk dijana daripada nama.'),
                    new Field('handle', 'Handle sosial', maxLength: 80, hint: 'cth: @waziabdulhamid'),
                    new Field('headline', 'Gelaran', hint: 'cth: Raja Kapcai / Raja Cub Prix'),
                    new Field('summary', 'Ringkasan', 'textarea', maxLength: 2000),
                    new Field('avatar', 'Gambar profil', 'image', imageWidth: 600),
                    new Field('cover_image', 'Gambar utama', 'image', imageWidth: 1600),
                    new Field('instagram_url', 'Instagram', 'url'),
                    new Field('facebook_url', 'Facebook', 'url'),
                    new Field('tiktok_url', 'TikTok', 'url'),
                    new Field('is_hero_of_month', 'Hero of the Month', 'checkbox'),
                    new Field('featured_month', 'Bulan ditampilkan', 'date', hint: 'Hero dengan bulan terkini dipaparkan di laman utama.'),
                    new Field('status', 'Status', 'select', required: true, options: $publish, default: 'draft'),
                ],
                columns: ['name' => ['Nama'], 'is_hero_of_month' => ['Hero', 'bool'], 'featured_month' => ['Bulan', 'date'], 'status' => ['Status', 'status']],
                orderBy: 'featured_month DESC, id DESC',
                search: ['name'],
                children: ['biodata', 'gambar-hof'],
                publicPath: '/hall-of-fame/{slug}',
            ),

            new Resource(
                key: 'biodata',
                label: 'Biodata',
                singular: 'bahagian biodata',
                table: 'hof_sections',
                fields: [
                    new Field('heading', 'Tajuk', required: true, maxLength: 160, hint: 'cth: Pendidikan, Kerjaya'),
                    new Field('body', 'Kandungan', 'textarea', required: true),
                    $sort,
                ],
                columns: ['heading' => ['Tajuk'], 'sort_order' => ['Susunan']],
                orderBy: 'sort_order ASC, id ASC',
                parentKey: 'hall-of-fame',
                foreignKey: 'hof_profile_id',
                titleColumn: 'heading',
            ),

            new Resource(
                key: 'gambar-hof',
                label: 'Galeri Profil',
                singular: 'gambar',
                table: 'hof_images',
                fields: [
                    new Field('path', 'Gambar', 'image', required: true, imageWidth: 1600),
                    new Field('alt_text', 'Teks alternatif'),
                    $sort,
                ],
                columns: ['path' => ['Gambar', 'image'], 'alt_text' => ['Teks alternatif'], 'sort_order' => ['Susunan']],
                orderBy: 'sort_order ASC, id ASC',
                parentKey: 'hall-of-fame',
                foreignKey: 'hof_profile_id',
                bulkImageField: 'path',
                titleColumn: 'alt_text',
            ),

            new Resource(
                key: 'banner',
                label: 'Banner',
                singular: 'banner',
                table: 'banners',
                fields: [
                    new Field('title', 'Tajuk', required: true, maxLength: 180),
                    new Field('placement', 'Kedudukan', 'select', required: true, default: 'global', options: [
                        'global' => 'Promosi atas footer (semua halaman)',
                        'home_hero' => 'Slaid latar hero laman utama',
                        'home_sponsor' => 'Jalur penaja laman utama',
                    ]),
                    new Field('body', 'Teks', 'textarea', maxLength: 500),
                    new Field('cta_label', 'Label butang', maxLength: 60),
                    new Field('link_url', 'Pautan butang', 'link', hint: 'Pautan penuh (https://...) atau laluan laman, cth: /aktiviti/pemenang'),
                    new Field('image_desktop', 'Gambar desktop', 'image', imageWidth: 2200),
                    new Field('image_mobile', 'Gambar mudah alih', 'image', imageWidth: 900),
                    new Field('alt_text', 'Teks alternatif'),
                    new Field('starts_on', 'Papar mulai', 'date'),
                    new Field('ends_on', 'Papar hingga', 'date'),
                    new Field('status', 'Status', 'select', required: true, options: $publish, default: 'draft'),
                    $sort,
                ],
                columns: ['title' => ['Tajuk'], 'placement' => ['Kedudukan'], 'image_desktop' => ['Gambar', 'image'], 'status' => ['Status', 'status']],
                orderBy: 'placement ASC, sort_order ASC, id ASC',
                search: ['title'],
            ),

            new Resource(
                key: 'statistik',
                label: 'Statistik Laman Utama',
                singular: 'statistik',
                table: 'site_stats',
                fields: [
                    new Field('value', 'Nilai', required: true, maxLength: 20, hint: 'cth: 30, 10+'),
                    new Field('label', 'Label', required: true, maxLength: 60, hint: 'cth: Destinasi'),
                    $sort,
                ],
                columns: ['value' => ['Nilai'], 'label' => ['Label'], 'sort_order' => ['Susunan']],
                orderBy: 'sort_order ASC, id ASC',
                titleColumn: 'label',
            ),
        ];

        $keyed = [];
        foreach ($resources as $resource) {
            $keyed[$resource->key] = $resource;
        }

        return $keyed;
    }
}
