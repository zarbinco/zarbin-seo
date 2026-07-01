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
                    <th>Status</th>
                    <th>Route</th>
                    <th>Label</th>
                    <th>Locale</th>
                    <th>Missing required</th>
                    <th>Warnings</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($routes as $item)
                    <tr>
                        <td>
                            <span class="zarbin-seo-status {{ $item->complete ? 'zarbin-seo-status-complete' : 'zarbin-seo-status-incomplete' }}" aria-label="{{ $item->statusLabel() }}">
                                {{ $item->statusSymbol() }}
                            </span>
                            {{ $item->statusLabel() }}
                        </td>
                        <td><code>{{ $item->key }}</code></td>
                        <td>{{ $item->label ?: '—' }}</td>
                        <td>{{ $item->locale ?: '—' }}</td>
                        <td>{{ $item->missing === [] ? '—' : implode(', ', $item->missing) }}</td>
                        <td>{{ $item->warnings === [] ? '—' : implode(', ', $item->warnings) }}</td>
                        <td>
                            @if($item->editUrl)
                                <a class="zarbin-seo-button zarbin-seo-button-secondary" href="{{ $item->editUrl }}">Edit</a>
                            @else
                                <span class="zarbin-seo-help">Edit route unavailable</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </section>
@endsection
