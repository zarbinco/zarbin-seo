<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

final class SitemapXml
{
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
