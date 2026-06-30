<?php

declare(strict_types=1);

namespace Zarbin\Seo\Generators;

use Throwable;
use Zarbin\Seo\Support\SitemapPathResolver;

final class RobotsTxtGenerator
{
    public function render(): string
    {
        if (! $this->enabled()) {
            return '';
        }

        $lines = [];
        $lines[] = 'User-agent: '.$this->userAgent();

        foreach ($this->stringList('zarbin-seo.robots_txt.allow') as $path) {
            $lines[] = 'Allow: '.$path;
        }

        foreach ($this->stringList('zarbin-seo.robots_txt.disallow') as $path) {
            $lines[] = 'Disallow: '.$path;
        }

        foreach ($this->sitemaps() as $sitemap) {
            $lines[] = 'Sitemap: '.$sitemap;
        }

        return implode(PHP_EOL, array_values(array_unique($lines))).PHP_EOL;
    }

    private function enabled(): bool
    {
        return (bool) $this->config('zarbin-seo.features.robots_txt', true)
            && (bool) $this->config('zarbin-seo.robots_txt.enabled', true);
    }

    private function userAgent(): string
    {
        $userAgent = $this->config('zarbin-seo.robots_txt.user_agent', '*');

        return is_string($userAgent) && trim($userAgent) !== '' ? trim($userAgent) : '*';
    }

    /**
     * @return array<int, string>
     */
    private function sitemaps(): array
    {
        $sitemaps = $this->stringList('zarbin-seo.robots_txt.sitemaps');

        if (
            $sitemaps !== []
            || ! (bool) $this->config('zarbin-seo.features.sitemap', true)
            || ! (bool) $this->config('zarbin-seo.sitemap.enabled', true)
        ) {
            return $sitemaps;
        }

        $url = SitemapPathResolver::urlForPath(SitemapPathResolver::indexPath());

        return trim($url) === '' ? [] : [$url];
    }

    /**
     * @return array<int, string>
     */
    private function stringList(string $key): array
    {
        $values = $this->config($key, []);

        if (! is_array($values)) {
            return [];
        }

        $normalized = [];

        foreach ($values as $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $value = trim((string) $value);

            if ($value !== '') {
                $normalized[] = $value;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function config(?string $key = null, mixed $default = null): mixed
    {
        if (! function_exists('config')) {
            return $default;
        }

        try {
            return config($key, $default);
        } catch (Throwable) {
            return $default;
        }
    }
}
