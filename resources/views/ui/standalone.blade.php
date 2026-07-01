<!doctype html>
<html lang="{{ $zarbinSeoLang ?? $uiLang ?? str_replace('_', '-', app()->getLocale()) }}" dir="{{ $zarbinSeoDir ?? $uiDir ?? \Zarbin\Seo\Support\UiDirection::current() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $zarbinSeoTitle ?? $pageTitle ?? \Zarbin\Seo\Support\UiTranslator::get('layout.standalone_title') }}</title>
    @include('zarbin-seo::ui.partials.styles')
</head>
<body class="zarbin-seo-standalone" dir="{{ $zarbinSeoDir ?? $uiDir ?? \Zarbin\Seo\Support\UiDirection::current() }}">
    @yield('zarbinSeoStandaloneContent')
</body>
</html>
