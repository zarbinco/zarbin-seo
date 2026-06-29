@extends('zarbin-seo::ui.layout', ['title' => 'Edit SEO Route Override', 'routeNamePrefix' => $routeNamePrefix])

@section('content')
    @unless($databaseReady)
        <div class="zarbin-seo-alert">
            Database overrides are not ready. The form is shown for preview, but saving is disabled.
        </div>
    @endunless

    <section>
        <h1>Edit Route Override</h1>
        <p><code>{{ $routeName }}</code>{{ $locale ? ' · '.$locale : '' }}</p>

        <form method="POST" action="{{ route($routeNamePrefix.'routes.update') }}">
            @csrf
            <input type="hidden" name="route" value="{{ $routeName }}">
            @if($locale)
                <input type="hidden" name="locale" value="{{ $locale }}">
            @endif

            @include('zarbin-seo::components.fields', ['fields' => $fields, 'values' => $values])

            <div class="zarbin-seo-actions">
                <button type="submit" @disabled(! $databaseReady)>Save override</button>
                <a class="zarbin-seo-button zarbin-seo-button-secondary" href="{{ route($routeNamePrefix.'routes.index') }}">Back</a>
            </div>
        </form>

        <form method="POST" action="{{ route($routeNamePrefix.'routes.delete') }}" style="margin-top: 12px;">
            @csrf
            @method('DELETE')
            <input type="hidden" name="route" value="{{ $routeName }}">
            @if($locale)
                <input type="hidden" name="locale" value="{{ $locale }}">
            @endif
            <button type="submit" class="zarbin-seo-button-secondary" @disabled(! $databaseReady)>Delete override</button>
        </form>
    </section>

    @if($showPreview)
        @include('zarbin-seo::components.preview', ['previewHtml' => $previewHtml])
    @endif
@endsection
