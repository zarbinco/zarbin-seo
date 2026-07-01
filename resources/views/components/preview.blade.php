@php
    $searchPreview = $searchPreview ?? null;
    $rawHtmlPreview = $rawHtmlPreview ?? ($previewHtml ?? '');
    $previewLocale = $searchPreview?->locale ?? ($uiLocale ?? null);
    $previewDir = $uiDir ?? \Zarbin\Seo\Support\UiDirection::current($previewLocale);
@endphp

<section class="zarbin-seo-panel" dir="{{ $previewDir }}">
    <h2>{{ \Zarbin\Seo\Support\UiTranslator::get('preview.title') }}</h2>

    @if($searchPreview)
        <div class="zarbin-seo-search-preview" dir="{{ $previewDir }}">
            <h3>{{ \Zarbin\Seo\Support\UiTranslator::get('preview.search_result') }}</h3>
            <div class="zarbin-seo-snippet" dir="{{ $previewDir }}">
                <div class="zarbin-seo-snippet-title">
                    {{ $searchPreview->hasTitle() ? $searchPreview->title : \Zarbin\Seo\Support\UiTranslator::get('preview.no_title') }}
                </div>
                <div class="zarbin-seo-snippet-url" dir="ltr">
                    {{ $searchPreview->hasUrl() ? $searchPreview->url : \Zarbin\Seo\Support\UiTranslator::get('preview.no_url') }}
                </div>
                <p class="zarbin-seo-snippet-description">
                    {{ $searchPreview->hasDescription() ? $searchPreview->description : \Zarbin\Seo\Support\UiTranslator::get('preview.no_description') }}
                </p>
            </div>

            @if($searchPreview->warnings !== [])
                <ul class="zarbin-seo-preview-warnings">
                    @foreach($searchPreview->warnings as $warning)
                        <li>{{ \Zarbin\Seo\Support\UiTranslator::get('preview.warnings.'.$warning) }}</li>
                    @endforeach
                </ul>
            @endif

            <p class="zarbin-seo-help">{{ \Zarbin\Seo\Support\UiTranslator::get('preview.approximation') }}</p>
        </div>
    @endif

    <div class="zarbin-seo-raw-preview">
        <h3>{{ \Zarbin\Seo\Support\UiTranslator::get('preview.raw_html') }}</h3>
        <textarea class="zarbin-seo-preview" readonly dir="ltr">{{ $rawHtmlPreview }}</textarea>
    </div>
</section>
