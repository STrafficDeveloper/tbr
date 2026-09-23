<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $template, array $data = []): string
    {
        return View::render($template, $data);
    }

    protected function seo(): Seo
    {
        return new Seo();
    }

    /** Rejects a POST whose CSRF token is missing or stale. */
    protected function verifyCsrf(Request $request): void
    {
        if (!Csrf::isValid($request->input(Csrf::fieldName()))) {
            http_response_code(419);
            exit('Sesi telah tamat. Sila muat semula halaman dan cuba lagi.');
        }
    }

    /** @param array<string,string> $values */
    protected function withInput(array $values): void
    {
        Session::put('_old', $values);
    }

    protected function clearInput(): void
    {
        Session::forget('_old');
    }
}
