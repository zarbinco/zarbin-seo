<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;

final class UiTranslator
{
    public static function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $translationKey = str_starts_with($key, 'zarbin-seo::')
            ? $key
            : 'zarbin-seo::ui.'.ltrim($key, '.');

        if (! function_exists('__')) {
            return self::fallback($key);
        }

        try {
            $translated = __($translationKey, $replace, $locale);
        } catch (Throwable) {
            return self::fallback($key);
        }

        return is_string($translated) && $translated !== $translationKey
            ? $translated
            : self::fallback($key);
    }

    public static function fieldLabel(string $field): string
    {
        return self::get("fields.{$field}.label");
    }

    public static function fieldHint(string $field): string
    {
        return self::get("fields.{$field}.hint");
    }

    private static function fallback(string $key): string
    {
        $key = preg_replace('/^zarbin-seo::ui\./', '', $key) ?? $key;
        $key = trim(str_replace(['.', '_', '-'], ' ', $key));

        return $key === '' ? '' : str($key)->title()->toString();
    }
}
