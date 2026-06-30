<?php

declare(strict_types=1);

namespace Zarbin\Seo\Http\Controllers;

use Illuminate\Http\Response;
use Zarbin\Seo\Generators\SitemapGenerator;
use Zarbin\Seo\Renderers\SitemapRenderer;
use Zarbin\Seo\Support\LocaleHelper;
use Zarbin\Seo\Support\SitemapPathResolver;

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

    public function localized(string $locale): Response|string
    {
        $locale = LocaleHelper::normalizeLocale($locale);

        if ($locale === null || ! array_key_exists($locale, SitemapPathResolver::localizedPaths())) {
            return response((new SitemapRenderer)->render([]), 200, [
                'Content-Type' => 'application/xml; charset=UTF-8',
            ]);
        }

        return response((new SitemapGenerator)->render($locale), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
