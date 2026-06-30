<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;

final class LocaleUrlStrategy
{
    private const CUSTOM = 'custom';

    private const PREFIXED_ALL = 'prefixed_all';

    private const DEFAULT_WITHOUT_PREFIX = 'default_without_prefix';

    /**
     * @return 'custom'|'prefixed_all'|'default_without_prefix'
     */
    public static function strategy(): string
    {
        $strategy = self::config('zarbin-seo.localization.url_strategy', self::CUSTOM);
        $strategy = is_string($strategy) ? trim($strategy) : self::CUSTOM;

        return in_array($strategy, [self::CUSTOM, self::PREFIXED_ALL, self::DEFAULT_WITHOUT_PREFIX], true)
            ? $strategy
            : self::CUSTOM;
    }

    public static function isPrefixedAll(): bool
    {
        return self::strategy() === self::PREFIXED_ALL;
    }

    public static function isDefaultWithoutPrefix(): bool
    {
        return self::strategy() === self::DEFAULT_WITHOUT_PREFIX;
    }

    public static function isCustom(): bool
    {
        return self::strategy() === self::CUSTOM;
    }

    public static function shouldPrefixLocale(?string $locale): bool
    {
        $locale = LocaleHelper::normalizeLocale($locale);

        if ($locale === null) {
            return false;
        }

        if (! self::localeIsConfigured($locale)) {
            return false;
        }

        if (self::isPrefixedAll()) {
            return true;
        }

        if (self::isDefaultWithoutPrefix()) {
            return $locale !== LocaleHelper::defaultLocale();
        }

        return false;
    }

    /**
     * @param  array<int|string, mixed>  $parameters
     * @return array<int|string, mixed>
     */
    public static function routeParametersForLocale(array $parameters = [], ?string $locale = null): array
    {
        $routeParameter = self::routeParameterName();
        $locale = LocaleHelper::normalizeLocale($locale);

        if ($routeParameter === null || $locale === null || array_key_exists($routeParameter, $parameters)) {
            return $parameters;
        }

        if (self::isDefaultWithoutPrefix() && $locale === LocaleHelper::defaultLocale()) {
            return $parameters;
        }

        return array_replace([$routeParameter => $locale], $parameters);
    }

    public static function localizedPath(string $path, ?string $locale = null): string
    {
        $path = self::normalizePath($path);
        $locale = LocaleHelper::normalizeLocale($locale);

        if ($locale === null || self::isCustom() || ! self::shouldPrefixLocale($locale)) {
            return self::isDefaultWithoutPrefix() && $locale !== null
                ? self::stripLeadingLocale($path, $locale)
                : $path;
        }

        $path = self::stripLeadingLocale($path, $locale);

        return $path === '' ? $locale : $locale.'/'.$path;
    }

    private static function routeParameterName(): ?string
    {
        $parameter = self::config('zarbin-seo.localization.route_parameter');

        if (! is_string($parameter)) {
            return null;
        }

        $parameter = trim($parameter);

        return $parameter === '' ? null : $parameter;
    }

    private static function localeIsConfigured(string $locale): bool
    {
        $locales = self::configuredLocales();

        return $locales === [] || in_array($locale, $locales, true);
    }

    /**
     * @return array<int, string>
     */
    private static function configuredLocales(): array
    {
        $locales = self::config('zarbin-seo.localization.locales', []);

        return is_array($locales) ? LocaleHelper::normalizeLocales($locales) : [];
    }

    private static function normalizePath(string $path): string
    {
        return trim(preg_replace('#/+#', '/', trim($path)) ?? '', '/');
    }

    private static function stripLeadingLocale(string $path, ?string $locale = null): string
    {
        if ($path === '') {
            return '';
        }

        $segments = explode('/', $path);
        $first = $segments[0] ?? '';
        $locales = self::configuredLocales();

        if (
            $first !== ''
            && (
                ($locale !== null && $first === $locale)
                || ($locales !== [] && in_array($first, $locales, true))
            )
        ) {
            array_shift($segments);

            return implode('/', $segments);
        }

        return $path;
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
