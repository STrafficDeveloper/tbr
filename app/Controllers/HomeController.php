<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Core\Request;
use App\Services\Schema;

final class HomeController extends Controller
{
    public function index(Request $request): string
    {
        $seo = $this->seo()
            ->setTitle('Komuniti Biker Malaysia')
            ->setDescription((string) Config::get('site.tagline'))
            ->setCanonical('/')
            ->addJsonLd(Schema::organization())
            ->addJsonLd(Schema::website());

        return $this->view('pages/home', ['seo' => $seo]);
    }
}
