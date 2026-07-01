<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Illuminate\Http\Response;
use Throwable;

final class XmlResponse
{
    private const DEFAULT_CONTENT_TYPE = 'application/xml; charset=UTF-8';

    public static function make(string $xml, int $status = 200): Response
    {
        $response = new Response($xml, $status);
        $response->headers->set('Content-Type', self::contentType());
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }

    private static function contentType(): string
    {
        $contentType = self::config('zarbin-seo.sitemap.content_type', self::DEFAULT_CONTENT_TYPE);

        if (! is_scalar($contentType)) {
            return self::DEFAULT_CONTENT_TYPE;
        }

        $contentType = trim((string) $contentType);

        if ($contentType === '' || ! str_contains($contentType, '/')) {
            return self::DEFAULT_CONTENT_TYPE;
        }

        return $contentType;
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
