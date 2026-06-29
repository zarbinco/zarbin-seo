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
    $warning = $warning ?? ($databaseReady ? null : 'SEO database overrides are not ready. Publish and run the migration, then enable database overrides.');
    $method = strtoupper((string) $method);
@endphp

@if($warning)
    <div class="zarbin-seo-alert">{{ $warning }}</div>
@endif

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
    <legend>SEO Override</legend>
    @include('zarbin-seo::components.fields', ['fields' => $fields, 'values' => $values])
</fieldset>

@if($standalone)
        <button type="submit" @disabled(! $databaseReady)>Save SEO override</button>
    </form>
@endif

@if($showPreview && $previewHtml !== '')
    @include('zarbin-seo::components.preview', ['previewHtml' => $previewHtml])
@endif
