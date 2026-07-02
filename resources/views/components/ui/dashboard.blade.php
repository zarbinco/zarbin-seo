@php($routeStats = $inventoryStats['routes'] ?? $routeStats ?? ['total' => 0, 'complete' => 0, 'incomplete' => 0])
@php($modelStats = $inventoryStats['models'] ?? $modelStats ?? ['total' => 0, 'complete' => 0, 'incomplete' => 0])

<div class="zarbin-seo-component zarbin-seo-dashboard" dir="{{ $uiDir }}" lang="{{ $uiLang }}" data-zarbin-seo-component="dashboard">
    @unless($databaseReady)
        <x-zarbin-seo::alert type="warning" :locale="$uiLocale">
            {{ \Zarbin\Seo\Support\UiTranslator::get('form.database_warning') }}
        </x-zarbin-seo::alert>
    @endunless

    <section>
        <h1>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.title') }}</h1>
        <p>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.description') }}</p>

        <table>
            <tbody>
            @foreach($status as $label => $value)
                <tr>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.status_items.'.$label) }}</th>
                    <td>{{ $value ? \Zarbin\Seo\Support\UiTranslator::get('dashboard.yes') : \Zarbin\Seo\Support\UiTranslator::get('dashboard.no') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.route_overrides') }}</h2>
        <p>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.route_overrides_description') }}</p>
        <table>
            <tbody>
                <tr>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.routes_total') }}</th>
                    <td>{{ $routeStats['total'] ?? 0 }}</td>
                </tr>
                <tr>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.routes_complete') }}</th>
                    <td>{{ $routeStats['complete'] ?? 0 }}</td>
                </tr>
                <tr>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.routes_incomplete') }}</th>
                    <td>{{ $routeStats['incomplete'] ?? 0 }}</td>
                </tr>
            </tbody>
        </table>
        @if($routeIndexUrl)
            <a class="zarbin-seo-button" href="{{ $routeIndexUrl }}">{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.manage_route_overrides') }}</a>
        @endif
    </section>

    @if($modelsEnabled)
        <section>
            <h2>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.model_overrides') }}</h2>
            <p>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.model_overrides_description') }}</p>
            <table>
                <tbody>
                    <tr>
                        <th>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.models_total') }}</th>
                        <td>{{ $modelStats['total'] ?? 0 }}</td>
                    </tr>
                    <tr>
                        <th>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.models_complete') }}</th>
                        <td>{{ $modelStats['complete'] ?? 0 }}</td>
                    </tr>
                    <tr>
                        <th>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.models_incomplete') }}</th>
                        <td>{{ $modelStats['incomplete'] ?? 0 }}</td>
                    </tr>
                </tbody>
            </table>
            @if($modelIndexUrl)
                <a class="zarbin-seo-button" href="{{ $modelIndexUrl }}">{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.manage_model_overrides') }}</a>
            @endif
        </section>
    @endif
</div>
