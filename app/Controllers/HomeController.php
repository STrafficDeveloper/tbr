<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Core\Request;

final class HomeController extends Controller
{
    public function index(Request $request): string
    {
        $seo = $this->seo()
            ->setTitle('Komuniti Biker Malaysia')
            ->setDescription((string) Config::get('site.tagline'))
            ->setCanonical('/')
            ->addJsonLd([
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => Config::get('app.name'),
                'url' => url('/'),
                'logo' => url('/assets/img/logo-tbr.svg'),
                'email' => Config::get('site.email'),
                'telephone' => Config::get('site.phone'),
                'parentOrganization' => ['@type' => 'Organization', 'name' => Config::get('site.company')],
            ]);

        return $this->view('pages/home', ['seo' => $seo]);
    }
}
