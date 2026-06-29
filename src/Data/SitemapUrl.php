<?php

declare(strict_types=1);

namespace Zarbin\Seo\Data;

use DateTimeInterface;

final readonly class SitemapUrl
{
    private const CHANGE_FREQUENCIES = [
        'always',
        'hourly',
        'daily',
        'weekly',
        'monthly',
        'yearly',
        'never',
    ];

    public string $loc;

    public DateTimeInterface|string|null $lastmod;

    public ?string $changefreq;

    public float|int|null $priority;

    /**
     * @var array<string, string>
     */
    public array $alternates;

    /**
     * @var array<string, mixed>
     */
    public array $extra;

    /**
     * @param  array<string, string>  $alternates
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        string $loc,
        DateTimeInterface|string|null $lastmod = null,
        ?string $changefreq = null,
        float|int|null $priority = null,
        array $alternates = [],
        array $extra = [],
    ) {
        $this->loc = trim($loc);
        $this->lastmod = $lastmod;
        $this->changefreq = self::normalizeChangeFrequency($changefreq);
        $this->priority = self::normalizePriority($priority);
        $this->alternates = self::normalizeAlternates($alternates);
        $this->extra = $extra;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function make(array $data): self
    {
        return new self(
            loc: trim((string) ($data['loc'] ?? '')),
            lastmod: $data['lastmod'] ?? null,
            changefreq: self::normalizeChangeFrequency($data['changefreq'] ?? null),
            priority: self::normalizePriority($data['priority'] ?? null),
            alternates: self::normalizeAlternates($data['alternates'] ?? []),
            extra: is_array($data['extra'] ?? null) ? $data['extra'] : [],
        );
    }

    /**
     * @return array{
     *     loc: string,
     *     lastmod: DateTimeInterface|string|null,
     *     changefreq: ?string,
     *     priority: float|int|null,
     *     alternates: array<string, string>,
     *     extra: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'loc' => trim($this->loc),
            'lastmod' => $this->lastmod,
            'changefreq' => self::normalizeChangeFrequency($this->changefreq),
            'priority' => self::normalizePriority($this->priority),
            'alternates' => self::normalizeAlternates($this->alternates),
            'extra' => $this->extra,
        ];
    }

    /**
     * @param  array<string, string>  $alternates
     */
    public function withAlternates(array $alternates): self
    {
        return self::make(array_replace($this->toArray(), [
            'alternates' => array_replace($this->alternates, $alternates),
        ]));
    }

    public function hasAlternates(): bool
    {
        return self::normalizeAlternates($this->alternates) !== [];
    }

    public function normalizedPriority(): ?string
    {
        $priority = self::normalizePriority($this->priority);

        return $priority === null ? null : number_format((float) $priority, 1, '.', '');
    }

    public function normalizedLastModified(): ?string
    {
        if ($this->lastmod instanceof DateTimeInterface) {
            return $this->lastmod->format(DateTimeInterface::ATOM);
        }

        if (is_string($this->lastmod)) {
            $lastmod = trim($this->lastmod);

            return $lastmod === '' ? null : $lastmod;
        }

        return null;
    }

    private static function normalizePriority(mixed $priority): float|int|null
    {
        if (! is_numeric($priority)) {
            return null;
        }

        return max(0.0, min(1.0, (float) $priority));
    }

    private static function normalizeChangeFrequency(mixed $changefreq): ?string
    {
        if (! is_string($changefreq)) {
            return null;
        }

        $changefreq = mb_strtolower(trim($changefreq));

        return in_array($changefreq, self::CHANGE_FREQUENCIES, true) ? $changefreq : null;
    }

    /**
     * @return array<string, string>
     */
    private static function normalizeAlternates(mixed $alternates): array
    {
        if (! is_array($alternates)) {
            return [];
        }

        $normalized = [];

        foreach ($alternates as $locale => $url) {
            $locale = trim((string) $locale);
            $url = trim((string) $url);

            if ($locale === '' || $url === '') {
                continue;
            }

            $normalized[$locale] = $url;
        }

        return $normalized;
    }
}
