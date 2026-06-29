@extends('zarbin-seo::ui.layout', ['title' => 'Zarbin SEO', 'routeNamePrefix' => $routeNamePrefix])

@section('content')
    @unless($databaseReady)
        <div class="zarbin-seo-alert">
            Database overrides are not ready. Enable database overrides and run the SEO meta migration before saving UI changes.
        </div>
    @endunless

    <section>
        <h1>Zarbin SEO</h1>
        <p>Package status and diagnostics.</p>

        <table>
            <tbody>
            @foreach($status as $label => $value)
                <tr>
                    <th>{{ str_replace('_', ' ', ucfirst($label)) }}</th>
                    <td>{{ $value ? 'Yes' : 'No' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Route Overrides</h2>
        <p>Edit manual SEO overrides for configured route-only pages.</p>
        <a class="zarbin-seo-button" href="{{ route($routeNamePrefix.'routes.index') }}">Manage route overrides</a>
    </section>
@endsection
