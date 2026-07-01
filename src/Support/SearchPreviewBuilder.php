<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;
use Zarbin\Seo\Data\SearchPreviewData;
use Zarbin\Seo\Data\SeoData;

final class SearchPreviewBuilder
{
    public function build(SeoData $data): SearchPreviewData
    {
        $title = $this->stringOrNull($data->title) ?? $this->stringOrNull($data->siteName);
        $url = $this->stringOrNull($data->canonical);
        $description = $this->stringOrNull($data->description);
        $warnings = [];

        if ($title === null) {
            $warnings[] = 'missing_title';
        } elseif (mb_strlen($title) > $this->titleLimit()) {
            $warnings[] = 'long_title';
        }

        if ($url === null) {
            $warnings[] = 'missing_url';
        }

        if ($description === null) {
            $warnings[] = 'missing_description';
        } elseif (mb_strlen($description) > $this->descriptionLimit()) {
            $warnings[] = 'long_description';
        }

        return SearchPreviewData::make([
            'title' => $title,
            'url' => $url,
            'description' => $description,
            'locale' => $data->locale,
            'warnings' => $warnings,
        ]);
    }

    private function titleLimit(): int
    {
        $limit = (int) $this->config('zarbin-seo.ui.preview.title_limit', 60);

        return $limit > 0 ? $limit : 60;
    }

    private function descriptionLimit(): int
    {
        $limit = $this->config('zarbin-seo.ui.preview.description_limit');

        if ($limit === null) {
            $limit = $this->config('zarbin-seo.defaults.description_limit', 160);
        }

        $limit = (int) $limit;

        return $limit > 0 ? $limit : 160;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function config(string $key, mixed $default = null): mixed
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
