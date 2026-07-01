@if(session('zarbin_seo_success'))
    <div class="zarbin-seo-alert zarbin-seo-alert-success">{{ session('zarbin_seo_success') }}</div>
@endif

@if(session('zarbin_seo_warning'))
    <div class="zarbin-seo-alert">{{ session('zarbin_seo_warning') }}</div>
@endif

@if(session('zarbin_seo_error'))
    <div class="zarbin-seo-alert zarbin-seo-alert-error">{{ session('zarbin_seo_error') }}</div>
@endif

@if(isset($errors) && $errors->any())
    <div class="zarbin-seo-alert zarbin-seo-alert-error">
        <strong>{{ \Zarbin\Seo\Support\UiTranslator::get('form.validation_errors') }}</strong>
    </div>
@endif
