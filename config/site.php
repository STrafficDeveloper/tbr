<?php

declare(strict_types=1);

use App\Core\Env;

return [
    'tagline' => 'Konvoi biker seluruh Malaysia. Dari komuniti jadi realiti.',
    'partnership' => 'The Bikers Ranger x Raja Kapcai "Ukhwah Bikers, Seantero Dunia"',
    'company' => 'Hive Marketing Sdn Bhd',
    'phone' => Env::get('CONTACT_PHONE', '+603-7666 9989'),
    'email' => Env::get('CONTACT_EMAIL', 'enquiry@hive-asia.com'),
    'whatsapp' => Env::get('WHATSAPP_NUMBER', '60376669989'),
    'privacy_note' => 'Nota privasi: Data digunakan untuk pengesahan & update tour sahaja.',

    'socials' => [
        'tbr' => [
            'label' => 'Ikuti The Bikers Ranger Di',
            'links' => [
                'facebook' => ['label' => 'Facebook The Bikers Ranger', 'url' => '#'],
                'instagram' => ['label' => 'Instagram The Bikers Ranger', 'url' => '#'],
                'tiktok' => ['label' => 'Tiktok The Bikers Ranger', 'url' => '#'],
            ],
        ],
        'raja_kapcai' => [
            'label' => 'Ikuti Raja Kapcai Di',
            'links' => [
                'facebook' => ['label' => 'Facebook Raja Kapcai', 'url' => '#'],
                'instagram' => ['label' => 'Instagram Raja Kapcai', 'url' => '#'],
                'tiktok' => ['label' => 'Tiktok Raja Kapcai', 'url' => '#'],
            ],
        ],
    ],

    // Single source of truth: the desktop bar and the mobile off-canvas both render this.
    'nav' => [
        ['label' => 'HOME', 'url' => '/'],
        [
            'label' => 'AKTIVITI TBR',
            'url' => '/aktiviti/event',
            'children' => [
                ['label' => 'Event', 'url' => '/aktiviti/event'],
                ['label' => 'Galeri', 'url' => '/aktiviti/galeri'],
                ['label' => 'Peraduan', 'url' => '/aktiviti/peraduan'],
                ['label' => 'Pemenang', 'url' => '/aktiviti/pemenang'],
            ],
        ],
        ['label' => 'PANAS ATAS JALAN', 'url' => '/panas-atas-jalan'],
        ['label' => 'PIT STOP REGISTRATION', 'url' => '/pit-stop/daftar'],
        ['label' => 'HALL OF FAME', 'url' => '/hall-of-fame'],
        ['label' => 'PORT RIDER', 'url' => '/port-rider'],
    ],

    'footer_nav' => [
        'TENTANG' => '/tentang',
        'STOP & TARIKH' => '/pit-stop',
        'DAFTAR' => '/daftar',
        'RAKAM TOUR' => '/aktiviti/galeri',
    ],

    'states' => [
        'johor' => 'Johor',
        'kedah' => 'Kedah',
        'kelantan' => 'Kelantan',
        'melaka' => 'Melaka',
        'negeri-sembilan' => 'Negeri Sembilan',
        'pahang' => 'Pahang',
        'perak' => 'Perak',
        'perlis' => 'Perlis',
        'pulau-pinang' => 'Pulau Pinang',
        'sabah' => 'Sabah',
        'sarawak' => 'Sarawak',
        'selangor' => 'Selangor',
        'terengganu' => 'Terengganu',
        'kuala-lumpur' => 'Kuala Lumpur',
        'labuan' => 'Labuan',
        'putrajaya' => 'Putrajaya',
    ],
];
