<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;

final class UiDirection
{
    public static function current(?string $locale = null): string
    {
        $mode = self::direction(self::config('zarbin-seo.ui.direction.mode', 'auto'));

        if ($mode === 'rtl' || $mode === 'ltr') {
            return $mode;
        }

        $locale = self::locale($locale);
        $language = self::language($locale);
        $rtlLocales = self::rtlLocales();

        return $language !== null && in_array($language, $rtlLocales, true)
            ? 'rtl'
            : self::fallback();
    }

    public static function isRtl(?string $locale = null): bool
    {
        return self::current($locale) === 'rtl';
    }

    public static function isLtr(?string $locale = null): bool
    {
        return self::current($locale) === 'ltr';
    }

    /**
     * @return array{dir: string, lang: string}
     */
    public static function htmlAttributes(?string $locale = null): array
    {
        $locale = self::locale($locale) ?? 'en';

        return [
            'dir' => self::current($locale),
            'lang' => str_replace('_', '-', $locale),
        ];
    }

    public static function textAlignStart(?string $locale = null): string
    {
        return self::isRtl($locale) ? 'right' : 'left';
    }

    public static function textAlignEnd(?string $locale = null): string
    {
        return self::isRtl($locale) ? 'left' : 'right';
    }

    private static function direction(mixed $value): string
    {
        $value = is_scalar($value) ? mb_strtolower(trim((string) $value)) : 'auto';

        return in_array($value, ['auto', 'rtl', 'ltr'], true) ? $value : 'auto';
    }

    private static function fallback(): string
    {
        $fallback = self::direction(self::config('zarbin-seo.ui.direction.fallback', 'ltr'));

        return $fallback === 'rtl' ? 'rtl' : 'ltr';
    }

    /**
     * @return array<int, string>
     */
    private static function rtlLocales(): array
    {
        $locales = self::config('zarbin-seo.ui.direction.rtl_locales', ['fa', 'ar', 'he', 'ur', 'ku', 'ckb', 'ps', 'sd', 'yi']);

        if (! is_array($locales)) {
            return ['fa', 'ar', 'he', 'ur', 'ku', 'ckb', 'ps', 'sd', 'yi'];
        }

        $normalized = [];

        foreach ($locales as $locale) {
            $language = self::language(is_scalar($locale) ? (string) $locale : null);

            if ($language !== null && ! in_array($language, $normalized, true)) {
                $normalized[] = $language;
            }
        }

        return $normalized;
    }

    private static function locale(?string $locale): ?string
    {
        if ($locale !== null && trim($locale) !== '') {
            return trim($locale);
        }

        try {
            if (function_exists('app') && method_exists(app(), 'getLocale')) {
                $appLocale = app()->getLocale();

                return is_string($appLocale) && trim($appLocale) !== '' ? trim($appLocale) : null;
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private static function language(?string $locale): ?string
    {
        if ($locale === null || trim($locale) === '') {
            return null;
        }

        $locale = str_replace('-', '_', trim($locale));
        $language = mb_strtolower(explode('_', $locale)[0] ?? '');

        return $language === '' ? null : $language;
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
