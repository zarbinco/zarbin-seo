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
    public function __invoke(): Response
    {
        return $this->xmlResponse((new SitemapGenerator)->render());
    }

    public function index(): Response
    {
        return $this->xmlResponse((new SitemapGenerator)->renderIndex());
    }

    public function localized(string $locale): Response
    {
        $locale = LocaleHelper::normalizeLocale($locale);

        if ($locale === null || ! array_key_exists($locale, SitemapPathResolver::localizedPaths())) {
            return $this->xmlResponse((new SitemapRenderer)->render([]));
        }

        return $this->xmlResponse((new SitemapGenerator)->render($locale));
    }

    private function xmlResponse(string $xml): Response
    {
        $response = new Response($xml, 200);
        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');

        return $response;
    }
}
