<?php

declare(strict_types=1);

namespace Zarbin\Seo\Http\Controllers;

use Illuminate\Http\Response;
use Zarbin\Seo\Generators\SitemapGenerator;

final class SitemapController
{
    public function __invoke(): Response|string
    {
        return response((new SitemapGenerator)->render(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function index(): Response|string
    {
        return response((new SitemapGenerator)->renderIndex(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
