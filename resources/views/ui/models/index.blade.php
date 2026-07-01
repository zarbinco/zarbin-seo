@extends('zarbin-seo::ui.layout', ['title' => \Zarbin\Seo\Support\UiTranslator::get('models.title'), 'routeNamePrefix' => $routeNamePrefix])

@section('content')
    @php($modelsEnabled = $modelsEnabled ?? \Zarbin\Seo\Support\UiConfig::modelInventoryEnabled())

    @unless($databaseReady)
        <div class="zarbin-seo-alert">
            {{ \Zarbin\Seo\Support\UiTranslator::get('form.database_warning') }}
        </div>
    @endunless

    <section>
        <h1>{{ \Zarbin\Seo\Support\UiTranslator::get('models.title') }}</h1>
        <p>{{ \Zarbin\Seo\Support\UiTranslator::get('models.description') }}</p>

        @if(! $modelsEnabled)
            <p>{{ \Zarbin\Seo\Support\UiTranslator::get('models.disabled') }}</p>
        @elseif($models === [])
            <p>{{ \Zarbin\Seo\Support\UiTranslator::get('models.empty') }}</p>
        @else
            <table>
                <thead>
                <tr>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.status') }}</th>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('models.class') }}</th>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('models.item') }}</th>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('models.key') }}</th>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.locale') }}</th>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.missing') }}</th>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.warnings') }}</th>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.actions') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($models as $item)
                    @php($missing = array_map(fn ($field) => \Zarbin\Seo\Support\UiTranslator::fieldLabel($field), $item->missing))
                    @php($warnings = array_map(fn ($field) => \Zarbin\Seo\Support\UiTranslator::fieldLabel($field), $item->warnings))
                    <tr>
                        <td>
                            <span class="zarbin-seo-status {{ $item->complete ? 'zarbin-seo-status-complete' : 'zarbin-seo-status-incomplete' }}" aria-label="{{ $item->statusLabel() }}">
                                {{ $item->statusSymbol() }}
                            </span>
                            {{ $item->statusLabel() }}
                        </td>
                        <td>{{ $item->meta['source_label'] ?? $item->meta['model_class'] ?? '-' }}</td>
                        <td>{{ $item->label ?: '-' }}</td>
                        <td><code>{{ $item->meta['model_key'] ?? $item->key }}</code></td>
                        <td>{{ $item->locale ?: '-' }}</td>
                        <td>{{ $missing === [] ? '-' : implode(', ', $missing) }}</td>
                        <td>{{ $warnings === [] ? '-' : implode(', ', $warnings) }}</td>
                        <td>
                            @if($item->editUrl)
                                <a class="zarbin-seo-button zarbin-seo-button-secondary" href="{{ $item->editUrl }}">{{ \Zarbin\Seo\Support\UiTranslator::get('models.edit') }}</a>
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
