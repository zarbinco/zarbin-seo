@if(session('zarbin_seo_success'))
    <div class="zarbin-seo-alert zarbin-seo-alert-success" dir="{{ $uiDir ?? \Zarbin\Seo\Support\UiDirection::current() }}">{{ session('zarbin_seo_success') }}</div>
@endif

@if(session('zarbin_seo_warning'))
    <div class="zarbin-seo-alert" dir="{{ $uiDir ?? \Zarbin\Seo\Support\UiDirection::current() }}">{{ session('zarbin_seo_warning') }}</div>
@endif

@if(session('zarbin_seo_error'))
    <div class="zarbin-seo-alert zarbin-seo-alert-error" dir="{{ $uiDir ?? \Zarbin\Seo\Support\UiDirection::current() }}">{{ session('zarbin_seo_error') }}</div>
@endif

@if(isset($errors) && $errors->any())
    <div class="zarbin-seo-alert zarbin-seo-alert-error" dir="{{ $uiDir ?? \Zarbin\Seo\Support\UiDirection::current() }}">
        <strong>{{ \Zarbin\Seo\Support\UiTranslator::get('form.validation_errors') }}</strong>
    </div>
@endif
