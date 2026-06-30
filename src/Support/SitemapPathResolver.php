<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;

final class SitemapPathResolver
{
    public static function indexPath(): string
    {
        return self::pathFromConfig('zarbin-seo.sitemap.index_path', 'sitemap_index.xml');
    }

    public static function defaultPath(): string
    {
        return self::pathFromConfig('zarbin-seo.sitemap.path', 'sitemap.xml');
    }

    /**
     * @return array<string, string>
     */
    public static function localizedPaths(): array
    {
        $paths = self::config('zarbin-seo.sitemap.localized_paths', []);

        if (! is_array($paths)) {
            return [];
        }

        $normalized = [];

        foreach ($paths as $locale => $path) {
            if (! is_scalar($locale) || ! is_scalar($path)) {
                continue;
            }

            $locale = LocaleHelper::normalizeLocale((string) $locale);
            $path = self::normalizePath((string) $path);

            if ($locale === null || $path === '') {
                continue;
            }

            $normalized[$locale] = $path;
        }

        return $normalized;
    }

    public static function pathForLocale(?string $locale = null): string
    {
        $locale = LocaleHelper::normalizeLocale($locale);
        $paths = self::localizedPaths();

        if ($locale !== null && isset($paths[$locale])) {
            return $paths[$locale];
        }

        return self::defaultPath();
    }

    public static function urlForPath(string $path): string
    {
        $path = self::normalizePath($path);
        $baseUrl = self::baseUrl();

        if ($baseUrl !== null) {
            return $path === '' ? $baseUrl : $baseUrl.'/'.$path;
        }

        $appUrl = self::config('app.url');

        if (is_string($appUrl) && trim($appUrl) !== '') {
            $base = rtrim(trim($appUrl), '/');

            return $path === '' ? $base : $base.'/'.$path;
        }

        if (function_exists('url')) {
            try {
                return (string) url($path === '' ? '/' : $path);
            } catch (Throwable) {
                // Fall through to a path-only URL.
            }
        }

        return $path;
    }

    public static function urlForLocale(?string $locale = null): string
    {
        return self::urlForPath(self::pathForLocale($locale));
    }

    /**
     * @return array<int, array{locale: string, path: string, loc: string}>
     */
    public static function localizedSitemapEntries(): array
    {
        $entries = [];

        foreach (self::localizedPaths() as $locale => $path) {
            $loc = trim(self::urlForPath($path));

            if ($loc === '') {
                continue;
            }

            $entries[] = [
                'locale' => $locale,
                'path' => $path,
                'loc' => $loc,
            ];
        }

        return $entries;
    }

    private static function pathFromConfig(string $key, string $default): string
    {
        $path = self::config($key, $default);
        $path = is_scalar($path) ? self::normalizePath((string) $path) : '';

        return $path === '' ? $default : $path;
    }

    private static function normalizePath(string $path): string
    {
        return trim(preg_replace('#/+#', '/', trim($path)) ?? '', '/');
    }

    private static function baseUrl(): ?string
    {
        $baseUrl = self::config('zarbin-seo.sitemap.base_url');

        if (! is_scalar($baseUrl)) {
            return null;
        }

        $baseUrl = rtrim(trim((string) $baseUrl), '/');

        return $baseUrl === '' ? null : $baseUrl;
    }

    private static function config(?string $key = null, mixed $default = null): mixed
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
