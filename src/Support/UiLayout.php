<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;

final class UiLayout
{
    public static function mode(): string
    {
        $mode = self::string('zarbin-seo.ui.layout.mode', 'standalone');

        if (! in_array($mode, ['standalone', 'host'], true)) {
            return 'standalone';
        }

        return $mode === 'host' && self::hostView() === null ? 'standalone' : $mode;
    }

    public static function isHostMode(): bool
    {
        return self::mode() === 'host';
    }

    public static function hostView(): ?string
    {
        $view = self::string('zarbin-seo.ui.layout.view');

        if ($view === '') {
            return null;
        }

        try {
            if (function_exists('view') && ! view()->exists($view)) {
                return null;
            }
        } catch (Throwable) {
            return null;
        }

        return $view;
    }

    public static function section(): string
    {
        return self::string('zarbin-seo.ui.layout.section', 'content') ?: 'content';
    }

    public static function titleSection(): string
    {
        return self::string('zarbin-seo.ui.layout.title_section', 'title') ?: 'title';
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public static function data(array $extra = []): array
    {
        $configured = self::config('zarbin-seo.ui.layout.data', []);
        $configured = is_array($configured) ? $configured : [];
        $locale = isset($extra['uiLocale']) && is_scalar($extra['uiLocale'])
            ? (string) $extra['uiLocale']
            : null;
        $attributes = UiDirection::htmlAttributes($locale);
        $title = isset($extra['pageTitle']) && is_scalar($extra['pageTitle'])
            ? (string) $extra['pageTitle']
            : UiTranslator::get('dashboard.title');

        return array_replace($configured, [
            'zarbinSeoDirection' => $attributes['dir'],
            'zarbinSeoDir' => $attributes['dir'],
            'zarbinSeoLang' => $attributes['lang'],
            'zarbinSeoTitle' => $title,
        ], $extra);
    }

    private static function string(string $key, ?string $default = null): string
    {
        $value = self::config($key, $default);

        if (! is_scalar($value)) {
            return $default ?? '';
        }

        return trim((string) $value);
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
