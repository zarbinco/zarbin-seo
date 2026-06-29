@extends('zarbin-seo::ui.layout', ['title' => 'SEO Route Overrides', 'routeNamePrefix' => $routeNamePrefix])

@section('content')
    @unless($databaseReady)
        <div class="zarbin-seo-alert">
            Database overrides are not ready. You can inspect resolved route SEO, but saving is disabled until the table is available.
        </div>
    @endunless

    <section>
        <h1>Route Overrides</h1>

        @if($routes === [])
            <p>No route SEO mappings are configured.</p>
        @else
            <table>
                <thead>
                <tr>
                    <th>Route</th>
                    <th>Configured title</th>
                    <th>Resolved title</th>
                    <th>Override</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($routes as $route)
                    <tr>
                        <td><code>{{ $route['name'] }}</code></td>
                        <td>{{ $route['configured_title'] ?: '—' }}</td>
                        <td>{{ $route['resolved_title'] ?: '—' }}</td>
                        <td>{{ $route['override_exists'] ? 'Saved' : 'None' }}</td>
                        <td>
                            <a class="zarbin-seo-button zarbin-seo-button-secondary" href="{{ route($routeNamePrefix.'routes.edit', ['route' => $route['name']]) }}">Edit</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </section>
@endsection
