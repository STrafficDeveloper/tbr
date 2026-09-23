<?php

declare(strict_types=1);

use App\Core\Env;

return [
    // "mail" uses PHP's mail() (works on standard cPanel hosting);
    // "log" writes messages to storage/logs/mail.log instead of sending.
    'driver' => Env::get('MAIL_DRIVER', 'mail'),
    'from' => Env::get('MAIL_FROM', 'enquiry@hive-asia.com'),
    'from_name' => Env::get('MAIL_FROM_NAME', 'The Bikers Ranger'),
];
