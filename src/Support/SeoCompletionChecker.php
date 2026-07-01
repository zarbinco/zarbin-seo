<?php

declare(strict_types=1);

namespace Zarbin\Seo\Support;

use Throwable;
use Zarbin\Seo\Data\SeoData;

final class SeoCompletionChecker
{
    private const DEFAULT_REQUIRED = [
        'title',
        'description',
        'canonical',
        'robots',
    ];

    private const DEFAULT_RECOMMENDED = [
        'image',
    ];

    private const SUPPORTED_FIELDS = [
        'title',
        'description',
        'canonical',
        'robots',
        'image',
        'type',
    ];

    /**
     * @return array{complete: bool, missing: array<int, string>, warnings: array<int, string>}
     */
    public function check(SeoData $data): array
    {
        if (! $this->enabled()) {
            return [
                'complete' => true,
                'missing' => [],
                'warnings' => [],
            ];
        }

        $missing = $this->missing($data);

        return [
            'complete' => $missing === [],
            'missing' => $missing,
            'warnings' => $this->warnings($data),
        ];
    }

    public function isComplete(SeoData $data): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        return $this->missing($data) === [];
    }

    /**
     * @return array<int, string>
     */
    public function missing(SeoData $data): array
    {
        if (! $this->enabled()) {
            return [];
        }

        return $this->missingFields($data, $this->configuredFields('required', self::DEFAULT_REQUIRED));
    }

    /**
     * @return array<int, string>
     */
    public function warnings(SeoData $data): array
    {
        if (! $this->enabled()) {
            return [];
        }

        return $this->missingFields($data, $this->configuredFields('recommended', self::DEFAULT_RECOMMENDED));
    }

    /**
     * @param  array<int, string>  $fields
     * @return array<int, string>
     */
    private function missingFields(SeoData $data, array $fields): array
    {
        $missing = [];

        foreach ($fields as $field) {
            if (! $this->fieldIsPresent($data, $field)) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    private function fieldIsPresent(SeoData $data, string $field): bool
    {
        return match ($field) {
            'title' => $data->hasTitle(),
            'description' => $data->hasDescription(),
            'canonical' => $data->hasCanonical(),
            'robots' => $data->robotsContent() !== '',
            'image' => $data->hasImage(),
            'type' => $data->type !== null && trim($data->type) !== '',
            default => true,
        };
    }

    /**
     * @param  array<int, string>  $default
     * @return array<int, string>
     */
    private function configuredFields(string $key, array $default): array
    {
        $fields = $this->config('zarbin-seo.ui.completion.'.$key, $default);

        if (! is_array($fields)) {
            return $default;
        }

        $normalized = [];

        foreach ($fields as $field) {
            if (! is_scalar($field)) {
                continue;
            }

            $field = trim((string) $field);

            if ($field === '' || ! in_array($field, self::SUPPORTED_FIELDS, true) || in_array($field, $normalized, true)) {
                continue;
            }

            $normalized[] = $field;
        }

        return $normalized;
    }

    private function enabled(): bool
    {
        return (bool) $this->config('zarbin-seo.ui.completion.enabled', true);
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
