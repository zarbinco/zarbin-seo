<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;

final class LocaleHelper
{
    public static function enabled(): bool
    {
        return (bool) self::config('zarbin-seo.localization.enabled', false);
    }

    /**
     * @return array<int, string>
     */
    public static function configuredLocales(): array
    {
        if (! self::enabled()) {
            return [];
        }

        $locales = self::config('zarbin-seo.localization.locales', []);

        return is_array($locales) ? self::normalizeLocales($locales) : [];
    }

    public static function defaultLocale(): ?string
    {
        return self::normalizeLocale(self::config('zarbin-seo.localization.default_locale'));
    }

    public static function currentLocale(?string $locale = null): ?string
    {
        $locale = self::normalizeLocale($locale);

        if ($locale !== null) {
            return $locale;
        }

        $locale = self::applicationLocale();

        if ($locale !== null) {
            return $locale;
        }

        return self::normalizeLocale(self::config('app.locale'))
            ?? self::defaultLocale();
    }

    public static function xDefault(): mixed
    {
        return self::config('zarbin-seo.localization.x_default');
    }

    public static function shouldGenerateHreflang(): bool
    {
        return (bool) self::config('zarbin-seo.localization.generate_hreflang', true);
    }

    public static function missingTranslationStrategy(): string
    {
        $strategy = (string) self::config('zarbin-seo.localization.missing_translation_strategy', 'hide');

        return self::isValidStrategy($strategy) ? $strategy : 'hide';
    }

    public static function isValidStrategy(string $strategy): bool
    {
        return in_array($strategy, ['hide', 'fallback', 'noindex'], true);
    }

    public static function normalizeLocale(?string $locale): ?string
    {
        if ($locale === null) {
            return null;
        }

        $locale = trim($locale);

        return $locale === '' ? null : $locale;
    }

    /**
     * @param  array<int|string, mixed>  $locales
     * @return array<int, string>
     */
    public static function normalizeLocales(array $locales): array
    {
        $normalized = [];

        foreach ($locales as $locale) {
            $locale = self::normalizeLocale(is_scalar($locale) ? (string) $locale : null);

            if ($locale === null || in_array($locale, $normalized, true)) {
                continue;
            }

            $normalized[] = $locale;
        }

        return $normalized;
    }

    private static function applicationLocale(): ?string
    {
        if (! function_exists('app')) {
            return null;
        }

        try {
            $app = app();

            return method_exists($app, 'getLocale')
                ? self::normalizeLocale($app->getLocale())
                : null;
        } catch (Throwable) {
            return null;
        }
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
