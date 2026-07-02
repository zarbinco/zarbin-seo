<div class="zarbin-seo-ui zarbin-seo-component zarbin-seo-panel-component" dir="{{ $uiDir }}" lang="{{ $uiLang }}" data-zarbin-seo-component="panel">
    <section>
        <h1>{{ \Zarbin\Seo\Support\UiTranslator::get('components.panel_title') }}</h1>
    </section>

    @if($showDashboard)
        <x-zarbin-seo::dashboard :locale="$locale" />
    @endif

    @if($showRoutes)
        <x-zarbin-seo::routes :locale="$locale" />
    @endif

    @if($showModels)
        <x-zarbin-seo::models :locale="$locale" />
    @endif
</div>
