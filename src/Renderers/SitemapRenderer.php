<?php

declare(strict_types=1);

namespace Zarbin\Seo\Renderers;

use DateTimeInterface;
use Zarbin\Seo\Data\SitemapUrl;
use Zarbin\Seo\Support\SitemapXml;

final class SitemapRenderer
{
    public function render(iterable $urls): string
    {
        $lines = [
            SitemapXml::xmlHeader(),
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">',
        ];

        foreach ($urls as $url) {
            $url = $url instanceof SitemapUrl ? $url : SitemapUrl::make((array) $url);

            if (trim($url->loc) === '') {
                continue;
            }

            $lines[] = '  <url>';
            $lines[] = '    <loc>'.SitemapXml::escape($url->loc).'</loc>';

            if ($url->normalizedLastModified() !== null) {
                $lines[] = '    <lastmod>'.SitemapXml::escape($url->normalizedLastModified()).'</lastmod>';
            }

            if ($url->changefreq !== null) {
                $lines[] = '    <changefreq>'.SitemapXml::escape($url->changefreq).'</changefreq>';
            }

            if ($url->normalizedPriority() !== null) {
                $lines[] = '    <priority>'.SitemapXml::escape($url->normalizedPriority()).'</priority>';
            }

            foreach ($url->alternates as $locale => $alternateUrl) {
                if (trim((string) $locale) === '' || trim((string) $alternateUrl) === '') {
                    continue;
                }

                $lines[] = '    <xhtml:link rel="alternate" hreflang="'.SitemapXml::escape((string) $locale).'" href="'.SitemapXml::escape((string) $alternateUrl).'" />';
            }

            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode(PHP_EOL, $lines);
    }

    public function renderIndex(iterable $sitemaps): string
    {
        $lines = [
            SitemapXml::xmlHeader(),
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($sitemaps as $sitemap) {
            $sitemap = (array) $sitemap;
            $loc = trim((string) ($sitemap['loc'] ?? ''));

            if ($loc === '') {
                continue;
            }

            $lines[] = '  <sitemap>';
            $lines[] = '    <loc>'.SitemapXml::escape($loc).'</loc>';

            $lastmod = $this->lastModified($sitemap['lastmod'] ?? null);

            if ($lastmod !== null) {
                $lines[] = '    <lastmod>'.SitemapXml::escape($lastmod).'</lastmod>';
            }

            $lines[] = '  </sitemap>';
        }

        $lines[] = '</sitemapindex>';

        return implode(PHP_EOL, $lines);
    }

    private function lastModified(mixed $lastmod): ?string
    {
        if ($lastmod instanceof DateTimeInterface) {
            return $lastmod->format(DateTimeInterface::ATOM);
        }

        if (is_string($lastmod)) {
            $lastmod = trim($lastmod);

            return $lastmod === '' ? null : $lastmod;
        }

        return null;
    }
}
