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
        $urlBlocks = [];
        $hasAlternates = false;

        foreach ($urls as $url) {
            $url = $url instanceof SitemapUrl ? $url : SitemapUrl::make((array) $url);

            if (trim($url->loc) === '') {
                continue;
            }

            $urlLines = [];
            $urlLines[] = '  <url>';
            $urlLines[] = '    <loc>'.SitemapXml::escape($url->loc).'</loc>';

            foreach ($url->alternates as $locale => $alternateUrl) {
                $link = $this->renderAlternateLink((string) $locale, (string) $alternateUrl);

                if ($link === null) {
                    continue;
                }

                $urlLines[] = '    '.$link;
                $hasAlternates = true;
            }

            if ($url->normalizedLastModified() !== null) {
                $urlLines[] = '    <lastmod>'.SitemapXml::escape($url->normalizedLastModified()).'</lastmod>';
            }

            if ($url->changefreq !== null) {
                $urlLines[] = '    <changefreq>'.SitemapXml::escape($url->changefreq).'</changefreq>';
            }

            if ($url->normalizedPriority() !== null) {
                $urlLines[] = '    <priority>'.SitemapXml::escape($url->normalizedPriority()).'</priority>';
            }

            $urlLines[] = '  </url>';
            $urlBlocks[] = $urlLines;
        }

        $lines = [
            SitemapXml::xmlHeader(),
            $this->urlsetOpenTag($hasAlternates),
        ];

        foreach ($urlBlocks as $urlLines) {
            array_push($lines, ...$urlLines);
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

    private function urlsetOpenTag(bool $hasAlternates): string
    {
        $attributes = 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';

        if ($hasAlternates) {
            $attributes .= ' xmlns:xhtml="http://www.w3.org/1999/xhtml"';
        }

        return '<urlset '.$attributes.'>';
    }

    private function renderAlternateLink(string $hreflang, string $href): ?string
    {
        $hreflang = trim($hreflang);
        $href = trim($href);

        if ($hreflang === '' || $href === '') {
            return null;
        }

        $attributes = SitemapXml::attributes([
            'rel' => 'alternate',
            'hreflang' => $hreflang,
            'href' => $href,
        ]);

        return $attributes === '' ? null : '<xhtml:link '.$attributes.' />';
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
