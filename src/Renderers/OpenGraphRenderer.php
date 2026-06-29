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
            'og:title' => $data->title ?: $data->siteName,
            'og:description' => $data->description,
            'og:url' => $data->canonical,
            'og:image' => $data->image,
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
