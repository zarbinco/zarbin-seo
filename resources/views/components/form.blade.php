@props([
    'source' => null,
    'locale' => null,
    'action' => null,
    'method' => 'POST',
    'standalone' => false,
    'showPreview' => true,
])

@php
    $repository = new \Zarbin\Seo\Repositories\SeoMetaRepository();
    $resolved = $resolved ?? ($source === null ? null : seo()->resolve($source, $locale));
    $databaseReady = $databaseReady ?? ($repository->enabled() && $repository->tableExists());
    $override = $override ?? null;

    if ($override === null && $databaseReady) {
        $override = is_string($source)
            ? $repository->findForRoute($source, $locale)
            : (is_object($source) ? $repository->findForSource($source, $locale) : null);
    }

    $fields = $fields ?? \Zarbin\Seo\Support\SeoFormFields::fields();
    $values = $values ?? \Zarbin\Seo\Support\SeoFormFields::values($override?->toArray() ?? [], $resolved?->toArray() ?? []);
    $showPreview = (bool) $showPreview && \Zarbin\Seo\Support\UiConfig::showPreview();
    $previewHtml = $previewHtml ?? ($resolved === null ? '' : seo()->renderer()->render($resolved));
    $rawHtmlPreview = $rawHtmlPreview ?? $previewHtml;
    $searchPreview = $searchPreview ?? ($resolved === null ? null : (new \Zarbin\Seo\Support\SearchPreviewBuilder())->build($resolved));
    $warning = $warning ?? ($databaseReady ? null : \Zarbin\Seo\Support\UiTranslator::get('form.database_setup_warning'));
    $uiLocale = $locale ?? $resolved?->locale;
    $uiAttributes = \Zarbin\Seo\Support\UiDirection::htmlAttributes($uiLocale);
    $uiDir = $uiDir ?? $uiAttributes['dir'];
    $uiLang = $uiLang ?? $uiAttributes['lang'];
    $uiTextStart = $uiTextStart ?? \Zarbin\Seo\Support\UiDirection::textAlignStart($uiLocale);
    $uiTextEnd = $uiTextEnd ?? \Zarbin\Seo\Support\UiDirection::textAlignEnd($uiLocale);
    $method = strtoupper((string) $method);
@endphp

@if($warning)
    <div class="zarbin-seo-alert" dir="{{ $uiDir }}" lang="{{ $uiLang }}">{{ $warning }}</div>
@endif

<div class="zarbin-seo-form" dir="{{ $uiDir }}" lang="{{ $uiLang }}" style="text-align: start;">
@if($standalone)
    <form method="{{ $method === 'GET' ? 'GET' : 'POST' }}" action="{{ $action ?? '' }}">
        @if($method !== 'GET')
            @csrf
        @endif
        @if(! in_array($method, ['GET', 'POST'], true))
            @method($method)
        @endif
@endif

@if(is_string($source))
    <input type="hidden" name="route" value="{{ $source }}">
@endif
@if($locale)
    <input type="hidden" name="locale" value="{{ $locale }}">
@endif

<fieldset @disabled(! $databaseReady)>
    <legend>{{ \Zarbin\Seo\Support\UiTranslator::get('form.legend') }}</legend>
    @include('zarbin-seo::components.fields', ['fields' => $fields, 'values' => $values])
</fieldset>

@if($standalone)
        <button type="submit" @disabled(! $databaseReady)>{{ \Zarbin\Seo\Support\UiTranslator::get('form.save') }}</button>
    </form>
@endif

@if($showPreview && $previewHtml !== '')
    @include('zarbin-seo::components.preview', ['searchPreview' => $searchPreview, 'previewHtml' => $previewHtml, 'rawHtmlPreview' => $rawHtmlPreview])
@endif
</div>
