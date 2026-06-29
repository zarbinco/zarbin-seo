<?php

declare(strict_types=1);

namespace Zarbin\Seo\Renderers;

use Throwable;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\Html;

final class OpenGraphRenderer
{
    public function render(SeoData $data): string
    {
        if (! $this->config('zarbin-seo.features.open_graph', true)) {
            return '';
        }

        return Html::lines(array_map(
            fn (array $attributes): string => Html::selfClosingTag('meta', $attributes),
            $this->tags($data)
        ));
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function tags(SeoData $data): array
    {
        $values = [
            'og:title' => $this->extraValue($data, 'og_title', 'open_graph', 'title') ?? ($data->title ?: $data->siteName),
            'og:description' => $this->extraValue($data, 'og_description', 'open_graph', 'description') ?? $data->description,
            'og:url' => $data->canonical,
            'og:image' => $this->extraValue($data, 'og_image', 'open_graph', 'image') ?? $data->image,
            'og:type' => $data->type ?: 'website',
            'og:site_name' => $data->siteName,
            'og:locale' => $data->locale,
        ];

        $tags = [];

        foreach ($values as $property => $content) {
            if ($content === null || $content === '') {
                continue;
            }

            $tags[] = ['property' => $property, 'content' => $content];
        }

        return $tags;
    }

    private function extraValue(SeoData $data, string $directKey, string $group, string $nestedKey): ?string
    {
        $value = $data->extra[$directKey] ?? null;

        if (! $this->filled($value)) {
            $value = isset($data->extra[$group]) && is_array($data->extra[$group])
                ? ($data->extra[$group][$nestedKey] ?? null)
                : null;
        }

        return $this->filled($value) && is_scalar($value) ? (string) $value : null;
    }

    private function filled(mixed $value): bool
    {
        return ! ($value === null || $value === '' || $value === []);
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
