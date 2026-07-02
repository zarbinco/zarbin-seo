<section class="zarbin-seo-component zarbin-seo-routes" dir="{{ $uiDir }}" lang="{{ $uiLang }}" data-zarbin-seo-component="routes">
    @unless($databaseReady)
        <x-zarbin-seo::alert type="warning" :locale="$uiLocale">
            {{ \Zarbin\Seo\Support\UiTranslator::get('form.database_warning') }}
        </x-zarbin-seo::alert>
    @endunless

    <h1>{{ \Zarbin\Seo\Support\UiTranslator::get('components.routes_title') }}</h1>
    <p>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.description') }}</p>

    @if($routes === [])
        <p>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.empty') }}</p>
    @else
        <table>
            <thead>
            <tr>
                <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.status') }}</th>
                <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.key') }}</th>
                <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.label') }}</th>
                <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.locale') }}</th>
                <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.missing') }}</th>
                <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.warnings') }}</th>
                @if($showActions)
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.actions') }}</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @foreach($routes as $index => $item)
                @php($missing = array_map(fn ($field) => \Zarbin\Seo\Support\UiTranslator::fieldLabel($field), $item->missing))
                @php($warnings = array_map(fn ($field) => \Zarbin\Seo\Support\UiTranslator::fieldLabel($field), $item->warnings))
                @php($actionUrl = $actionUrls[$index] ?? null)
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
                    @if($showActions)
                        <td>
                            @if($actionUrl)
                                <a class="zarbin-seo-button zarbin-seo-button-secondary" href="{{ $actionUrl }}">{{ \Zarbin\Seo\Support\UiTranslator::get('routes.edit') }}</a>
                            @else
                                <span class="zarbin-seo-help">{{ \Zarbin\Seo\Support\UiTranslator::get('routes.edit_unavailable') }}</span>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</section>
