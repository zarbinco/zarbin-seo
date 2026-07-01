@extends('zarbin-seo::ui.layout', ['title' => \Zarbin\Seo\Support\UiTranslator::get('dashboard.title'), 'routeNamePrefix' => $routeNamePrefix])

@section('content')
    @php($inventoryStats = $inventoryStats ?? ['total' => 0, 'complete' => 0, 'incomplete' => 0])

    @unless($databaseReady)
        <div class="zarbin-seo-alert">
            {{ \Zarbin\Seo\Support\UiTranslator::get('form.database_warning') }}
        </div>
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
                    <td>{{ $inventoryStats['total'] ?? 0 }}</td>
                </tr>
                <tr>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.routes_complete') }}</th>
                    <td>{{ $inventoryStats['complete'] ?? 0 }}</td>
                </tr>
                <tr>
                    <th>{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.routes_incomplete') }}</th>
                    <td>{{ $inventoryStats['incomplete'] ?? 0 }}</td>
                </tr>
            </tbody>
        </table>
        <a class="zarbin-seo-button" href="{{ route($routeNamePrefix.'routes.index') }}">{{ \Zarbin\Seo\Support\UiTranslator::get('dashboard.manage_route_overrides') }}</a>
    </section>
@endsection
