@php
    $alertDir = $uiDir ?? \Zarbin\Seo\Support\UiDirection::current($locale ?? null);
    $alertLang = $uiLang ?? \Zarbin\Seo\Support\UiDirection::htmlAttributes($locale ?? null)['lang'];
    $type = $type ?? null;
    $session = $session ?? true;
    $componentMessage = $message ?? null;

    if ($componentMessage === null && isset($slot) && trim((string) $slot) !== '') {
        $componentMessage = trim((string) $slot);
    }

    $typeClass = match ($type) {
        'success' => ' zarbin-seo-alert-success',
        'error', 'danger' => ' zarbin-seo-alert-error',
        default => '',
    };
@endphp

@if($componentMessage !== null && $componentMessage !== '')
    <div class="zarbin-seo-alert{{ $typeClass }}" dir="{{ $alertDir }}" lang="{{ $alertLang }}" data-zarbin-seo-component="alert">{{ $componentMessage }}</div>
@endif

@if($session)
    @if(session('zarbin_seo_success'))
        <div class="zarbin-seo-alert zarbin-seo-alert-success" dir="{{ $alertDir }}" lang="{{ $alertLang }}" data-zarbin-seo-component="alert">{{ session('zarbin_seo_success') }}</div>
    @endif

    @if(session('zarbin_seo_warning'))
        <div class="zarbin-seo-alert" dir="{{ $alertDir }}" lang="{{ $alertLang }}" data-zarbin-seo-component="alert">{{ session('zarbin_seo_warning') }}</div>
    @endif

    @if(session('zarbin_seo_error'))
        <div class="zarbin-seo-alert zarbin-seo-alert-error" dir="{{ $alertDir }}" lang="{{ $alertLang }}" data-zarbin-seo-component="alert">{{ session('zarbin_seo_error') }}</div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="zarbin-seo-alert zarbin-seo-alert-error" dir="{{ $alertDir }}" lang="{{ $alertLang }}" data-zarbin-seo-component="alert">
            <strong>{{ \Zarbin\Seo\Support\UiTranslator::get('form.validation_errors') }}</strong>
        </div>
    @endif
@endif
