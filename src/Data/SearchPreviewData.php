<?php

declare(strict_types=1);

namespace Zarbin\Seo\Data;

final readonly class SearchPreviewData
{
    /**
     * @param  array<int, string>  $warnings
     */
    public function __construct(
        public ?string $title = null,
        public ?string $url = null,
        public ?string $description = null,
        public ?string $locale = null,
        public array $warnings = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data = []): self
    {
        return new self(
            title: self::stringOrNull($data['title'] ?? null),
            url: self::stringOrNull($data['url'] ?? null),
            description: self::stringOrNull($data['description'] ?? null),
            locale: self::stringOrNull($data['locale'] ?? null),
            warnings: self::warnings($data['warnings'] ?? []),
        );
    }

    public static function fromSeoData(SeoData $data): self
    {
        return self::make([
            'title' => $data->title,
            'url' => $data->canonical,
            'description' => $data->description,
            'locale' => $data->locale,
        ]);
    }

    /**
     * @return array{title: ?string, url: ?string, description: ?string, locale: ?string, warnings: array<int, string>}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'url' => $this->url,
            'description' => $this->description,
            'locale' => $this->locale,
            'warnings' => $this->warnings,
        ];
    }

    public function hasTitle(): bool
    {
        return $this->title !== null && $this->title !== '';
    }

    public function hasUrl(): bool
    {
        return $this->url !== null && $this->url !== '';
    }

    public function hasDescription(): bool
    {
        return $this->description !== null && $this->description !== '';
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<int, string>
     */
    private static function warnings(mixed $warnings): array
    {
        if (! is_array($warnings)) {
            return [];
        }

        $normalized = [];

        foreach ($warnings as $warning) {
            if (! is_scalar($warning)) {
                continue;
            }

            $warning = trim((string) $warning);

            if ($warning !== '' && ! in_array($warning, $normalized, true)) {
                $normalized[] = $warning;
            }
        }

        return $normalized;
    }
}
