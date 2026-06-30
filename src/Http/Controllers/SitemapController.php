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
        return $this->xmlResponse((new SitemapGenerator)->render());
    }

    public function index(): Response|string
    {
        return $this->xmlResponse((new SitemapGenerator)->renderIndex());
    }

    public function localized(string $locale): Response|string
    {
        $locale = LocaleHelper::normalizeLocale($locale);

        if ($locale === null || ! array_key_exists($locale, SitemapPathResolver::localizedPaths())) {
            return $this->xmlResponse((new SitemapRenderer)->render([]));
        }

        return $this->xmlResponse((new SitemapGenerator)->render($locale));
    }

    private function xmlResponse(string $xml): Response|string
    {
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
