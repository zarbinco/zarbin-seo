<?php

declare(strict_types=1);

namespace Zarbin\Seo\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\SearchPreviewBuilder;
use Zarbin\Seo\Support\UiComponentDataFactory;

final class Preview extends Component
{
    public function __construct(
        public ?SeoData $data = null,
        public mixed $searchPreview = null,
        public ?string $previewHtml = null,
        public ?string $rawHtmlPreview = null,
        public ?string $locale = null,
        public bool $showRawHtml = true,
    ) {}

    public function render(): View
    {
        $previewHtml = $this->previewHtml ?? ($this->data === null ? '' : seo()->renderer()->render($this->data));
        $searchPreview = $this->searchPreview ?? ($this->data === null ? null : (new SearchPreviewBuilder)->build($this->data));
        $locale = $this->locale ?? $this->data?->locale ?? $searchPreview?->locale;

        return view('zarbin-seo::components.preview', array_replace(
            (new UiComponentDataFactory)->directionData($locale),
            [
                'seoData' => $this->data,
                'searchPreview' => $searchPreview,
                'previewHtml' => $previewHtml,
                'rawHtmlPreview' => $this->rawHtmlPreview ?? $previewHtml,
                'showRawHtml' => $this->showRawHtml,
            ],
        ));
    }
}
