<?php

declare(strict_types=1);

namespace Zarbin\Seo\Data;

final readonly class SeoInventoryItem
{
    /**
     * @param  array<int, string>  $missing
     * @param  array<int, string>  $warnings
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public string $key,
        public string $type,
        public ?string $label,
        public ?string $locale,
        public ?string $editUrl,
        public SeoData $data,
        public bool $complete,
        public array $missing,
        public array $warnings,
        public array $meta = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'type' => $this->type,
            'label' => $this->label,
            'locale' => $this->locale,
            'edit_url' => $this->editUrl,
            'data' => $this->data->toArray(),
            'complete' => $this->complete,
            'missing' => $this->missing,
            'warnings' => $this->warnings,
            'meta' => $this->meta,
        ];
    }

    public function statusLabel(): string
    {
        return $this->complete ? 'Complete' : 'Incomplete';
    }

    public function statusSymbol(): string
    {
        return $this->complete ? '✓' : '×';
    }
}
