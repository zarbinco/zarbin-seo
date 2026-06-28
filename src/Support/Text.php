<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Illuminate\Support\Str;

final class Text
{
    public static function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = strip_tags($value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/[\s\x{00A0}]+/u', ' ', $value) ?? $value;
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    public static function limit(?string $value, int $limit = 160): ?string
    {
        $value = self::clean($value);

        if ($value === null) {
            return null;
        }

        if ($limit <= 0) {
            return null;
        }

        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        if (class_exists(Str::class)) {
            $limited = Str::limit($value, $limit, '', true);

            if ($limited !== '') {
                return $limited;
            }
        }

        $truncated = mb_substr($value, 0, $limit);
        $lastSpacePosition = mb_strrpos($truncated, ' ');

        if ($lastSpacePosition !== false && $lastSpacePosition >= (int) floor($limit * 0.6)) {
            return rtrim(mb_substr($truncated, 0, $lastSpacePosition));
        }

        return rtrim($truncated);
    }
}
