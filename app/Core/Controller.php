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
            echo View::render('pages/errors/419', [
                'seo' => (new Seo())->setTitle('Sesi Telah Tamat')->noIndex(),
                'hidePromos' => true,
                'backUrl' => Response::safeReferer('/'),
            ]);
            exit;
        }
    }

    /**
     * Post/Redirect/Get on validation failure: the form is re-shown with the
     * member's input and the messages, and a refresh cannot re-submit it.
     *
     * @param array<string,string> $errors
     * @param array<string,string|null> $input never pass passwords here
     */
    protected function backWithErrors(string $path, array $errors, array $input = []): never
    {
        Session::flash('_errors', $errors);
        Session::flash('_old', $input);
        Response::redirect($path);
    }

    protected function redirectWithStatus(string $path, string $message, string $type = 'success'): never
    {
        Session::flash('_status', ['type' => $type, 'message' => $message]);
        Response::redirect($path);
    }
}
