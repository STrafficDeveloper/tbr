<?php

declare(strict_types=1);

use App\Core\Env;

return [
    'name' => Env::get('APP_NAME', 'The Bikers Ranger'),
    'env' => Env::get('APP_ENV', 'production'),
    'debug' => Env::get('APP_DEBUG', false) === true,
    'url' => Env::get('APP_URL', 'http://localhost:8000'),
    'locale' => 'ms-MY',
    'default_description' => 'Komuniti The Bikers Ranger — konvoi biker seluruh Malaysia. '
        . 'Sertai pit stop, peraduan dan galeri komuniti. Dari komuniti jadi realiti.',
];
