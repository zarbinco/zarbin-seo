<div class="zarbin-seo-component zarbin-seo-route-form {{ $standalone ? 'zarbin-seo-component-standalone' : '' }}" dir="{{ $uiDir }}" lang="{{ $uiLang }}" data-zarbin-seo-component="route-form">
    @if($warning)
        <x-zarbin-seo::alert type="warning" :locale="$uiLocale">{{ $warning }}</x-zarbin-seo::alert>
    @endif

    @if($routeConfigured)
        <section>
            <h1>{{ \Zarbin\Seo\Support\UiTranslator::get('routes.edit_title') }}</h1>
            <p><code dir="ltr">{{ $routeName }}</code>{{ $locale ? ' - '.$locale : '' }}</p>

            <form method="POST" action="{{ $action ?? '' }}">
                @csrf
                <input type="hidden" name="route" value="{{ $routeName }}">
                @if($locale)
                    <input type="hidden" name="locale" value="{{ $locale }}">
                @endif

                <fieldset @disabled(! $canSubmit)>
                    <legend>{{ \Zarbin\Seo\Support\UiTranslator::get('form.legend') }}</legend>
                    @include('zarbin-seo::components.fields', ['fields' => $fields, 'values' => $values])
                </fieldset>

                <div class="zarbin-seo-actions">
                    <button type="submit" @disabled(! $canSubmit)>{{ \Zarbin\Seo\Support\UiTranslator::get('form.save_override') }}</button>
                    @if($routeIndexUrl)
                        <a class="zarbin-seo-button zarbin-seo-button-secondary" href="{{ $routeIndexUrl }}">{{ \Zarbin\Seo\Support\UiTranslator::get('navigation.back_to_routes') }}</a>
                    @endif
                </div>
            </form>

            @if($deleteAction)
                <form method="POST" action="{{ $deleteAction }}" style="margin-top: 12px;">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="route" value="{{ $routeName }}">
                    @if($locale)
                        <input type="hidden" name="locale" value="{{ $locale }}">
                    @endif
                    <button type="submit" class="zarbin-seo-button-secondary" @disabled(! $canDelete)>{{ \Zarbin\Seo\Support\UiTranslator::get('form.delete') }}</button>
                </form>
            @endif
        </section>

        @if($showPreview && $previewHtml !== '')
            @include('zarbin-seo::components.preview', [
                'searchPreview' => $searchPreview,
                'previewHtml' => $previewHtml,
                'rawHtmlPreview' => $rawHtmlPreview,
                'showRawHtml' => $showRawHtml,
                'uiLocale' => $uiLocale,
                'uiDir' => $uiDir,
                'uiLang' => $uiLang,
            ])
        @endif
    @endif
</div>
