<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;

final class Html
{
    public static function escape(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        if (function_exists('e')) {
            try {
                return e($value);
            } catch (Throwable) {
                // Fall through to the native escaper.
            }
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function attributes(array $attributes): string
    {
        $rendered = [];

        foreach ($attributes as $name => $value) {
            if ($value === null || $value === false) {
                continue;
            }

            if ($value === true) {
                $rendered[] = self::escape((string) $name);

                continue;
            }

            $rendered[] = self::escape((string) $name).'="'.self::escape((string) $value).'"';
        }

        return $rendered === [] ? '' : ' '.implode(' ', $rendered);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function tag(string $name, array $attributes = [], ?string $content = null): string
    {
        return '<'.$name.self::attributes($attributes).'>'.self::escape($content).'</'.$name.'>';
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function selfClosingTag(string $name, array $attributes = []): string
    {
        return '<'.$name.self::attributes($attributes).'>';
    }

    /**
     * @param  array<int, string|null>  $lines
     */
    public static function lines(array $lines, bool $minify = false): string
    {
        $rendered = [];

        foreach ($lines as $line) {
            if ($line === null) {
                continue;
            }

            foreach (preg_split('/\R/u', $line) ?: [] as $part) {
                if (trim($part) === '') {
                    continue;
                }

                $rendered[] = $part;
            }
        }

        return implode($minify ? '' : PHP_EOL, $rendered);
    }
}
