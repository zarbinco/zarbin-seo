<?php

declare(strict_types=1);

namespace Zarbin\Seo\Renderers;

use Throwable;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Schema\ProductSchemaBuilder;
use Zarbin\Seo\Support\Html;

final class JsonLdRenderer
{
    public function render(SeoData $data): string
    {
        if (! $this->config('zarbin-seo.features.schema', true)) {
            return '';
        }

        $payload = (new ProductSchemaBuilder)->build($data);

        if ($payload !== null) {
            return $this->script($payload);
        }

        $name = $data->title ?: $data->siteName;

        if ($name === null || $name === '') {
            return '';
        }

        $payload = array_filter([
            '@context' => 'https://schema.org',
            '@type' => $data->type ?: 'WebPage',
            'name' => $name,
            'description' => $data->description,
            'url' => $data->canonical,
            'image' => $data->image,
        ], fn (mixed $value): bool => $value !== null && $value !== '');

        return $this->script($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function script(array $payload): string
    {
        $json = json_encode($payload, $this->jsonFlags());

        if ($json === false) {
            return '';
        }

        return '<script'.Html::attributes(['type' => 'application/ld+json']).'>'.$json.'</script>';
    }

    private function jsonFlags(): int
    {
        $flags = JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_HEX_TAG
            | JSON_HEX_APOS
            | JSON_HEX_AMP
            | JSON_HEX_QUOT;

        if ($this->config('zarbin-seo.rendering.pretty_json', false)) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return $flags;
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
