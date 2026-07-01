<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

final class SitemapXml
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function attributes(array $attributes): string
    {
        $rendered = [];

        foreach ($attributes as $name => $value) {
            if ($value === null || (! is_scalar($value) && ! $value instanceof \Stringable)) {
                continue;
            }

            $name = trim((string) $name);
            $value = trim((string) $value);

            if ($name === '' || $value === '') {
                continue;
            }

            $rendered[] = $name.'="'.self::escape($value).'"';
        }

        return implode(' ', $rendered);
    }

    public static function escape(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function xmlHeader(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>';
    }
}
