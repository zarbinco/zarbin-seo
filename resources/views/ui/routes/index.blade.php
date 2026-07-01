@extends('zarbin-seo::ui.layout', ['title' => \Zarbin\Seo\Support\UiTranslator::get('routes.title'), 'routeNamePrefix' => $routeNamePrefix])

@section('content')
    @unless($databaseReady)
        <div class="zarbin-seo-alert">
            {{ \Zarbin\Seo\Support\UiTranslator::get('form.database_warning') }}
        </div>
    @endunless

    <section dir="{{ $uiDir ?? \Zarbin\Seo\Support\UiDirection::current() }}">
        <h1>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.title') }}</h1>
        <p>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.description') }}</p>

        @if($routes === [])
            <p>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.empty') }}</p>
        @else
            <table dir="{{ $uiDir ?? \Zarbin\Seo\Support\UiDirection::current() }}">
                <thead>
                <tr>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.status') }}</th>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.key') }}</th>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.label') }}</th>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.locale') }}</th>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.missing') }}</th>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.warnings') }}</th>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($routes as $item)
                    @php($missing = array_map(fn ($field) => \Zarbin\Seo\Support\UiTranslator::fieldLabel($field), $item->missing))
                    @php($warnings = array_map(fn ($field) => \Zarbin\Seo\Support\UiTranslator::fieldLabel($field), $item->warnings))
                    <tr>
                        <td>
                            <span class="zarbin-seo-status {{ $item->complete ? 'zarbin-seo-status-complete' : 'zarbin-seo-status-incomplete' }}" aria-label="{{ $item->statusLabel() }}">
                                {{ $item->statusSymbol() }}
                            </span>
                            {{ $item->statusLabel() }}
                        </td>
                        <td><code dir="ltr">{{ $item->key }}</code></td>
                        <td>{{ $item->label ?: '-' }}</td>
                        <td>{{ $item->locale ?: '-' }}</td>
                        <td>{{ $missing === [] ? '-' : implode(', ', $missing) }}</td>
                        <td>{{ $warnings === [] ? '-' : implode(', ', $warnings) }}</td>
                        <td>
                            @if($item->editUrl)
                                <a class="zarbin-seo-button zarbin-seo-button-secondary" href="{{ $item->editUrl }}">{{ \Zarbin\Seo\Support\UiTranslator::get('routes.edit') }}</a>
                            @else
                                <span class="zarbin-seo-help">{{ \Zarbin\Seo\Support\UiTranslator::get('routes.edit_unavailable') }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </section>
@endsection
