<?php

declare(strict_types=1);

namespace Zarbin\Seo\Http\Controllers;

use Illuminate\Http\Response;
use Zarbin\Seo\Generators\SitemapGenerator;
use Zarbin\Seo\Renderers\SitemapRenderer;
use Zarbin\Seo\Support\LocaleHelper;
use Zarbin\Seo\Support\SitemapPathResolver;
use Zarbin\Seo\Support\XmlResponse;

final class SitemapController
{
    public function __invoke(): Response
    {
        return XmlResponse::make((new SitemapGenerator)->render());
    }

    public function index(): Response
    {
        return XmlResponse::make((new SitemapGenerator)->renderIndex());
    }

    public function localized(string $locale): Response
    {
        $locale = LocaleHelper::normalizeLocale($locale);

        if ($locale === null || ! array_key_exists($locale, SitemapPathResolver::localizedPaths())) {
            return XmlResponse::make((new SitemapRenderer)->render([]));
        }

        return XmlResponse::make((new SitemapGenerator)->render($locale));
    }
}
