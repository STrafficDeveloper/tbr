<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Last line of defence for web requests: anything uncaught is logged in full
 * and the visitor gets a branded 500 page, never a blank screen or a stack
 * trace (unless APP_DEBUG is on).
 */
final class ErrorHandler
{
    private const FATAL = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];

    public static function register(bool $debug): void
    {
        set_exception_handler(static function (Throwable $exception) use ($debug): void {
            error_log(sprintf(
                "Uncaught %s: %s in %s:%d\n%s",
                $exception::class,
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTraceAsString(),
            ));

            self::render($debug ? $exception::class . ': ' . $exception->getMessage() . "\n\n" . $exception->getTraceAsString() : null);
        });

        // Fatal errors (e.g. out of memory) bypass the exception handler.
        register_shutdown_function(static function () use ($debug): void {
            $error = error_get_last();

            if ($error !== null && in_array($error['type'], self::FATAL, true)) {
                self::render($debug ? "{$error['message']} in {$error['file']}:{$error['line']}" : null);
            }
        });
    }

    private static function render(?string $debugDetail): void
    {
        // Drop any half-rendered page so the error page isn't glued to it.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
            header('Cache-Control: no-store');
        }

        // Standalone on purpose: no layout, no database. It has to work when
        // the thing that failed is the database.
        $detail = $debugDetail;
        require BASE_PATH . '/views/pages/errors/500.php';
    }
}
