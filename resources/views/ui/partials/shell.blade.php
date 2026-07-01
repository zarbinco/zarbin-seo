@php($prefix = $routeNamePrefix ?? \Zarbin\Seo\Support\UiConfig::routeNamePrefix())

<div class="zarbin-seo-ui" dir="{{ $uiDir ?? \Zarbin\Seo\Support\UiDirection::current() }}" lang="{{ $uiLang ?? str_replace('_', '-', app()->getLocale()) }}" data-direction="{{ $uiDirection ?? $uiDir ?? \Zarbin\Seo\Support\UiDirection::current() }}">
    <header>
        <div>
            <strong>Zarbin SEO</strong>
        </div>
        <nav aria-label="{{ \Zarbin\Seo\Support\UiTranslator::get('navigation.aria') }}">
            <a href="{{ route($prefix.'dashboard') }}">{{ \Zarbin\Seo\Support\UiTranslator::get('navigation.dashboard') }}</a>
            <a href="{{ route($prefix.'routes.index') }}">{{ \Zarbin\Seo\Support\UiTranslator::get('navigation.routes') }}</a>
            @if(\Zarbin\Seo\Support\UiConfig::modelInventoryEnabled() && \Illuminate\Support\Facades\Route::has($prefix.'models.index'))
                <a href="{{ route($prefix.'models.index') }}">{{ \Zarbin\Seo\Support\UiTranslator::get('navigation.models') }}</a>
            @endif
        </nav>
    </header>
    <main>
        @include('zarbin-seo::components.alert')
        @yield('content')
    </main>
</div>
