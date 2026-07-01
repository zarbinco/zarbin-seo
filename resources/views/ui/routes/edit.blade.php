@extends('zarbin-seo::ui.layout', ['title' => \Zarbin\Seo\Support\UiTranslator::get('routes.edit_title'), 'routeNamePrefix' => $routeNamePrefix])

@section('content')
    @php($previewHtml = $previewHtml ?? '')
    @php($rawHtmlPreview = $rawHtmlPreview ?? $previewHtml)
    @php($searchPreview = $searchPreview ?? (isset($resolved) ? (new \Zarbin\Seo\Support\SearchPreviewBuilder())->build($resolved) : null))

    @unless($databaseReady)
        <div class="zarbin-seo-alert">
            {{ \Zarbin\Seo\Support\UiTranslator::get('form.database_preview_warning') }}
        </div>
    @endunless

    <section>
        <h1>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.edit_title') }}</h1>
        <p><code>{{ $routeName }}</code>{{ $locale ? ' · '.$locale : '' }}</p>

        <form method="POST" action="{{ route($routeNamePrefix.'routes.update') }}">
            @csrf
            <input type="hidden" name="route" value="{{ $routeName }}">
            @if($locale)
                <input type="hidden" name="locale" value="{{ $locale }}">
            @endif

            @include('zarbin-seo::components.fields', ['fields' => $fields, 'values' => $values])

            <div class="zarbin-seo-actions">
                <button type="submit" @disabled(! $databaseReady)>{{ \Zarbin\Seo\Support\UiTranslator::get('form.save_override') }}</button>
                <a class="zarbin-seo-button zarbin-seo-button-secondary" href="{{ route($routeNamePrefix.'routes.index') }}">{{ \Zarbin\Seo\Support\UiTranslator::get('navigation.back_to_routes') }}</a>
            </div>
        </form>

        <form method="POST" action="{{ route($routeNamePrefix.'routes.delete') }}" style="margin-top: 12px;">
            @csrf
            @method('DELETE')
            <input type="hidden" name="route" value="{{ $routeName }}">
            @if($locale)
                <input type="hidden" name="locale" value="{{ $locale }}">
            @endif
            <button type="submit" class="zarbin-seo-button-secondary" @disabled(! $databaseReady)>{{ \Zarbin\Seo\Support\UiTranslator::get('form.delete') }}</button>
        </form>
    </section>

    @if($showPreview)
        @include('zarbin-seo::components.preview', ['searchPreview' => $searchPreview, 'previewHtml' => $previewHtml, 'rawHtmlPreview' => $rawHtmlPreview])
    @endif
@endsection
