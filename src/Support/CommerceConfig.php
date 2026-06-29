<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;

final class CommerceConfig
{
    public static function enabled(): bool
    {
        return (bool) self::config('zarbin-seo.features.commerce', false)
            && (bool) self::config('zarbin-seo.commerce.enabled', false);
    }

    public static function defaultCurrency(?string $locale = null): ?string
    {
        $currency = self::config('zarbin-seo.commerce.default_currency');

        return self::normalizeCurrency($currency);
    }

    public static function currencyForLocale(?string $locale = null): ?string
    {
        $locale = $locale === null ? null : trim($locale);

        if ($locale !== null && $locale !== '') {
            $currency = self::config('zarbin-seo.commerce.currency_per_locale.'.$locale);

            if ($currency !== null) {
                return self::normalizeCurrency($currency);
            }
        }

        return self::defaultCurrency($locale);
    }

    /**
     * @return array<string, mixed>
     */
    public static function modelConfig(object|string $source): array
    {
        $class = is_object($source) ? get_class($source) : $source;
        $config = self::config('zarbin-seo.models.'.$class.'.commerce', []);

        return is_array($config) ? $config : [];
    }

    private static function normalizeCurrency(mixed $currency): ?string
    {
        if (! is_scalar($currency)) {
            return null;
        }

        $currency = trim((string) $currency);

        return $currency === '' ? null : mb_strtoupper($currency);
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
