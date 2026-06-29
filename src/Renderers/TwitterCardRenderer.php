<?php

declare(strict_types=1);

namespace Zarbin\Seo\Renderers;

use Throwable;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\Html;

final class TwitterCardRenderer
{
    public function render(SeoData $data): string
    {
        if (! $this->config('zarbin-seo.features.twitter', true)) {
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
        $image = $this->extraValue($data, 'twitter_image', 'twitter', 'image') ?? $data->image;

        $values = [
            'twitter:card' => $image !== null && $image !== '' ? 'summary_large_image' : 'summary',
            'twitter:title' => $this->extraValue($data, 'twitter_title', 'twitter', 'title') ?? ($data->title ?: $data->siteName),
            'twitter:description' => $this->extraValue($data, 'twitter_description', 'twitter', 'description') ?? $data->description,
            'twitter:image' => $image,
        ];

        $tags = [];

        foreach ($values as $name => $content) {
            if ($content === null || $content === '') {
                continue;
            }

            $tags[] = ['name' => $name, 'content' => $content];
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
