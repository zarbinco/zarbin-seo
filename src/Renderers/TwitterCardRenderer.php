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
        $values = [
            'twitter:card' => $data->hasImage() ? 'summary_large_image' : 'summary',
            'twitter:title' => $data->title ?: $data->siteName,
            'twitter:description' => $data->description,
            'twitter:image' => $data->image,
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
