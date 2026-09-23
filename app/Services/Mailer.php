<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use InvalidArgumentException;

/**
 * Plain-text transactional mail. Uses PHP mail() by default so it works on
 * standard cPanel hosting; MAIL_DRIVER=log writes to a file instead for
 * local development, where there is no mail server.
 */
final class Mailer
{
    public function send(string $to, string $subject, string $body): bool
    {
        // A newline in a header value would let input inject extra headers (e.g. Bcc).
        if (preg_match('/[\r\n]/', $to . $subject) === 1 || filter_var($to, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Invalid mail recipient or subject.');
        }

        $from = (string) Config::get('mail.from');
        $fromName = (string) Config::get('mail.from_name');
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

        if (Config::get('mail.driver') === 'log') {
            $entry = sprintf("[%s] To: %s\nSubject: %s\n\n%s\n%s\n", date('c'), $to, $subject, $body, str_repeat('-', 60));

            return file_put_contents(BASE_PATH . '/storage/logs/mail.log', $entry, FILE_APPEND | LOCK_EX) !== false;
        }

        $headers = implode("\r\n", [
            'From: =?UTF-8?B?' . base64_encode($fromName) . '?= <' . $from . '>',
            'Reply-To: ' . $from,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
        ]);

        // -f sets the envelope sender so bounces and SPF checks use our domain.
        return mail($to, $encodedSubject, $body, $headers, '-f' . $from);
    }
}
