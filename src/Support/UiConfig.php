<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;

final class UiConfig
{
    public static function enabled(): bool
    {
        return (bool) self::config('zarbin-seo.features.ui', false)
            && (bool) self::config('zarbin-seo.ui.enabled', false);
    }

    public static function routeEnabled(): bool
    {
        return self::enabled()
            && (bool) self::value('route_enabled', self::config('zarbin-seo.ui.route.enabled', false));
    }

    public static function path(): string
    {
        $path = self::stringValue('path', self::config('zarbin-seo.ui.route.path', 'admin/seo'));
        $path = trim($path, '/');

        return $path === '' ? 'admin/seo' : $path;
    }

    public static function routeNamePrefix(): string
    {
        $name = self::stringValue('name', self::config('zarbin-seo.ui.route.name', 'zarbin-seo.ui.'));

        return trim($name) === '' ? 'zarbin-seo.ui.' : trim($name);
    }

    /**
     * @return array<int, string>
     */
    public static function middleware(): array
    {
        $middleware = self::value('middleware', []);

        if (is_string($middleware)) {
            return trim($middleware) === '' ? [] : [trim($middleware)];
        }

        if (! is_array($middleware)) {
            return [];
        }

        return array_values(array_filter(
            array_map(fn (mixed $value): string => is_scalar($value) ? trim((string) $value) : '', $middleware),
            fn (string $value): bool => $value !== ''
        ));
    }

    public static function gate(): ?string
    {
        $gate = self::stringValue('gate', self::config('zarbin-seo.ui.route.gate'));

        return trim($gate) === '' ? null : trim($gate);
    }

    public static function databaseRequired(): bool
    {
        return (bool) self::value('database_required', true);
    }

    public static function showPreview(): bool
    {
        return (bool) self::value('show_preview', true);
    }

    private static function value(string $key, mixed $default = null): mixed
    {
        $value = self::config('zarbin-seo.ui.'.$key);

        return $value === null ? $default : $value;
    }

    private static function stringValue(string $key, mixed $default = null): string
    {
        $value = self::value($key, $default);

        return is_scalar($value) ? (string) $value : '';
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
