<?php

declare(strict_types=1);

namespace Zarbin\Seo\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Models\SeoMeta;
use Zarbin\Seo\Repositories\SeoMetaRepository;
use Zarbin\Seo\Support\SeoFormFields;
use Zarbin\Seo\Support\UiConfig;

final class Form extends Component
{
    public function __construct(
        public mixed $source = null,
        public ?string $locale = null,
        public ?string $action = null,
        public string $method = 'POST',
        public bool $standalone = false,
        public bool $showPreview = true,
    ) {}

    public function render(): View
    {
        $repository = new SeoMetaRepository;
        $resolved = $this->resolvedData();
        $override = $this->override($repository);
        $databaseReady = $repository->enabled() && $repository->tableExists();

        return view('zarbin-seo::components.form', [
            'source' => $this->source,
            'locale' => $this->locale,
            'action' => $this->action,
            'method' => strtoupper($this->method),
            'standalone' => $this->standalone,
            'showPreview' => $this->showPreview && UiConfig::showPreview(),
            'fields' => SeoFormFields::fields(),
            'values' => SeoFormFields::values($override?->toArray() ?? [], $resolved?->toArray() ?? []),
            'resolved' => $resolved,
            'previewHtml' => $resolved === null ? '' : seo()->renderer()->render($resolved),
            'databaseReady' => $databaseReady,
            'warning' => $databaseReady ? null : 'SEO database overrides are not ready. Publish and run the migration, then enable database overrides.',
        ]);
    }

    private function resolvedData(): ?SeoData
    {
        if ($this->source === null) {
            return null;
        }

        return seo()->resolve($this->source, $this->locale);
    }

    private function override(SeoMetaRepository $repository): ?SeoMeta
    {
        if (! $repository->enabled() || ! $repository->tableExists()) {
            return null;
        }

        if (is_string($this->source)) {
            return $repository->findForRoute($this->source, $this->locale);
        }

        if (is_object($this->source)) {
            return $repository->findForSource($this->source, $this->locale);
        }

        return null;
    }
}
