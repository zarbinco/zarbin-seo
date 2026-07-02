<div class="zarbin-seo-component zarbin-seo-model-form {{ $standalone ? 'zarbin-seo-component-standalone' : '' }}" dir="{{ $uiDir }}" lang="{{ $uiLang }}" data-zarbin-seo-component="model-form">
    @if($warning)
        <x-zarbin-seo::alert type="warning" :locale="$uiLocale">{{ $warning }}</x-zarbin-seo::alert>
    @endif

    @if($sourceFound)
        <section>
            <h1>{{ \Zarbin\Seo\Support\UiTranslator::get('models.edit_title') }}</h1>
            <p>
                {{ $sourceLabel ?? $modelClass }}
                -
                {{ $modelLabel ?? $modelKey }}
                <br>
                <code dir="ltr">{{ $modelClass }}</code>
                <br>
                {{ \Zarbin\Seo\Support\UiTranslator::get('models.key') }}:
                <code dir="ltr">{{ $modelKey }}</code>{{ $locale ? ' - '.$locale : '' }}
            </p>

            <form method="POST" action="{{ $action ?? '' }}">
                @csrf
                @if($modelToken)
                    <input type="hidden" name="model" value="{{ $modelToken }}">
                @endif
                @if($modelKey)
                    <input type="hidden" name="id" value="{{ $modelKey }}">
                @endif
                @if($locale)
                    <input type="hidden" name="locale" value="{{ $locale }}">
                @endif

                <fieldset @disabled(! $canSubmit)>
                    <legend>{{ \Zarbin\Seo\Support\UiTranslator::get('form.legend') }}</legend>
                    @include('zarbin-seo::components.fields', ['fields' => $fields, 'values' => $values])
                </fieldset>

                <div class="zarbin-seo-actions">
                    <button type="submit" @disabled(! $canSubmit)>{{ \Zarbin\Seo\Support\UiTranslator::get('form.save_override') }}</button>
                    @if($modelIndexUrl)
                        <a class="zarbin-seo-button zarbin-seo-button-secondary" href="{{ $modelIndexUrl }}">{{ \Zarbin\Seo\Support\UiTranslator::get('navigation.back_to_models') }}</a>
                    @endif
                </div>
            </form>

            @if($deleteAction)
                <form method="POST" action="{{ $deleteAction }}" style="margin-top: 12px;">
                    @csrf
                    @method('DELETE')
                    @if($modelToken)
                        <input type="hidden" name="model" value="{{ $modelToken }}">
                    @endif
                    @if($modelKey)
                        <input type="hidden" name="id" value="{{ $modelKey }}">
                    @endif
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
