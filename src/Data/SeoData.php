<?php

declare(strict_types=1);

namespace Zarbin\Seo\Data;

final readonly class SeoData
{
    /**
     * @param  array<int, string>|string|null  $robots
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $canonical = null,
        string|array|null $robots = [],
        public ?string $image = null,
        public ?string $type = null,
        public ?string $locale = null,
        public ?string $siteName = null,
        public ?string $separator = null,
        public array $extra = [],
    ) {
        $this->robots = self::normalizeRobots($robots);
    }

    /**
     * @var array<int, string>
     */
    public array $robots;

    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data = []): self
    {
        return self::fromArray($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $source = $data;
        $extra = isset($source['extra']) && is_array($source['extra'])
            ? $source['extra']
            : [];

        foreach (array_keys(self::knownKeys()) as $key) {
            unset($data[$key]);
        }

        return new self(
            title: self::stringOrNull($source['title'] ?? null),
            description: self::stringOrNull($source['description'] ?? null),
            canonical: self::stringOrNull($source['canonical'] ?? null),
            robots: $source['robots'] ?? [],
            image: self::stringOrNull($source['image'] ?? null),
            type: self::stringOrNull($source['type'] ?? null),
            locale: self::stringOrNull($source['locale'] ?? null),
            siteName: self::stringOrNull($source['siteName'] ?? null),
            separator: self::stringOrNull($source['separator'] ?? null),
            extra: array_replace($data, $extra),
        );
    }

    /**
     * @return array{
     *     title: ?string,
     *     description: ?string,
     *     canonical: ?string,
     *     robots: array<int, string>,
     *     image: ?string,
     *     type: ?string,
     *     locale: ?string,
     *     siteName: ?string,
     *     separator: ?string,
     *     extra: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'canonical' => $this->canonical,
            'robots' => $this->robots,
            'image' => $this->image,
            'type' => $this->type,
            'locale' => $this->locale,
            'siteName' => $this->siteName,
            'separator' => $this->separator,
            'extra' => $this->extra,
        ];
    }

    /**
     * @param  array<string, mixed>|self  $data
     */
    public function merge(array|self $data): self
    {
        $incoming = $data instanceof self ? $data->toArray() : $data;
        $current = $this->toArray();

        if (
            isset($incoming['extra'], $current['extra'])
            && is_array($incoming['extra'])
            && is_array($current['extra'])
        ) {
            $incoming['extra'] = array_replace($current['extra'], $incoming['extra']);
        }

        return self::fromArray(array_replace($current, $incoming));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function with(array $data): self
    {
        return $this->merge($data);
    }

    public function withTitle(?string $title): self
    {
        return $this->with(['title' => $title]);
    }

    public function withDescription(?string $description): self
    {
        return $this->with(['description' => $description]);
    }

    public function withCanonical(?string $canonical): self
    {
        return $this->with(['canonical' => $canonical]);
    }

    public function withRobots(string|array|null $robots): self
    {
        return $this->with(['robots' => $robots]);
    }

    public function withImage(?string $image): self
    {
        return $this->with(['image' => $image]);
    }

    public function withType(?string $type): self
    {
        return $this->with(['type' => $type]);
    }

    public function withLocale(?string $locale): self
    {
        return $this->with(['locale' => $locale]);
    }

    public function withSiteName(?string $siteName): self
    {
        return $this->with(['siteName' => $siteName]);
    }

    public function withSeparator(?string $separator): self
    {
        return $this->with(['separator' => $separator]);
    }

    public function robotsContent(): string
    {
        return implode(', ', $this->robots);
    }

    public function hasTitle(): bool
    {
        return $this->title !== null && $this->title !== '';
    }

    public function hasDescription(): bool
    {
        return $this->description !== null && $this->description !== '';
    }

    public function hasCanonical(): bool
    {
        return $this->canonical !== null && $this->canonical !== '';
    }

    public function hasImage(): bool
    {
        return $this->image !== null && $this->image !== '';
    }

    /**
     * @return array<string, true>
     */
    private static function knownKeys(): array
    {
        return [
            'title' => true,
            'description' => true,
            'canonical' => true,
            'robots' => true,
            'image' => true,
            'type' => true,
            'locale' => true,
            'siteName' => true,
            'separator' => true,
            'extra' => true,
        ];
    }

    /**
     * @param  array<int|string, mixed>|string|null  $robots
     * @return array<int, string>
     */
    private static function normalizeRobots(string|array|null $robots): array
    {
        if ($robots === null) {
            return [];
        }

        $items = is_string($robots) ? explode(',', $robots) : $robots;
        $normalized = [];

        foreach ($items as $item) {
            foreach (explode(',', (string) $item) as $value) {
                $value = trim($value);

                if ($value === '' || in_array($value, $normalized, true)) {
                    continue;
                }

                $normalized[] = $value;
            }
        }

        return $normalized;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) $value;
    }
}
