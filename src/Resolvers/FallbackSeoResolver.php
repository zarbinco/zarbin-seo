<?php

declare(strict_types=1);

namespace Zarbin\Seo\Resolvers;

use Throwable;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\AttributeReader;
use Zarbin\Seo\Support\Text;

final class FallbackSeoResolver
{
    public function resolve(mixed $source = null, ?string $locale = null): SeoData
    {
        $descriptionLimit = $this->descriptionLimit();
        $content = AttributeReader::first($source, ['content']);
        $description = AttributeReader::first($source, ['excerpt', 'summary', 'description']);
        $title = AttributeReader::first($source, ['title', 'name']);

        if ($description === null && $content !== null) {
            $description = Text::limit((string) $content, $descriptionLimit);
        } else {
            $description = Text::limit(
                $description === null ? $this->config('zarbin-seo.defaults.description') : (string) $description,
                $descriptionLimit
            );
        }

        if ($this->isEmpty($title)) {
            $title = $this->config('zarbin-seo.defaults.title');
        }

        if ($this->isEmpty($title)) {
            $title = $this->config('app.name');
        }

        return SeoData::make([
            'title' => Text::clean($this->stringOrNull($title)),
            'description' => $description,
            'image' => AttributeReader::first($source, [
                'image',
                'image_url',
                'cover',
                'cover_image',
                'cover_image_url',
            ], $this->config('zarbin-seo.defaults.image')),
            'robots' => $this->config('zarbin-seo.defaults.robots', 'index, follow'),
            'separator' => $this->config('zarbin-seo.defaults.separator', '|'),
            'siteName' => $this->config('app.name'),
            'locale' => $locale ?? $this->applicationLocale() ?? $this->config('app.locale'),
        ]);
    }

    private function descriptionLimit(): int
    {
        return (int) $this->config('zarbin-seo.defaults.description_limit', 160);
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null || is_array($value)) {
            return null;
        }

        return (string) $value;
    }

    private function applicationLocale(): ?string
    {
        if (! function_exists('app')) {
            return null;
        }

        try {
            $app = app();

            return method_exists($app, 'getLocale') ? $app->getLocale() : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function config(?string $key = null, mixed $default = null): mixed
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
