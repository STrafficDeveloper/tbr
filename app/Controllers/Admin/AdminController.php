<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Seo;
use App\Core\View;

/**
 * Every admin controller extends this, and the access check lives in the
 * constructor: an admin action can't be added without it.
 */
abstract class AdminController extends Controller
{
    public function __construct()
    {
        Auth::requireLogin(new Request());

        // Members who aren't admins get a plain 404: no hint the area exists.
        if (!Auth::isAdmin()) {
            Response::notFound();
        }

        header('X-Robots-Tag: noindex, nofollow');
        header('Cache-Control: no-store');
    }

    /** @param array<string,mixed> $data */
    protected function adminView(string $template, string $title, array $data = []): string
    {
        return View::render('admin/' . $template, $data + [
            'seo' => (new Seo())->setTitle($title . ' | Admin')->noIndex(),
            'pageTitle' => $title,
        ], 'layouts/admin');
    }
}
