@php
    $pageTitle = $pageTitle ?? $title ?? \Zarbin\Seo\Support\UiTranslator::get('layout.standalone_title');
    $uiLocale = $uiLocale ?? null;
    $uiAttributes = \Zarbin\Seo\Support\UiDirection::htmlAttributes($uiLocale);
    $uiDir = $uiDir ?? $uiAttributes['dir'];
    $uiDirection = $uiDirection ?? $uiDir;
    $uiLang = $uiLang ?? $uiAttributes['lang'];
    $uiIsRtl = $uiIsRtl ?? ($uiDir === 'rtl');
    $uiTextStart = $uiTextStart ?? \Zarbin\Seo\Support\UiDirection::textAlignStart($uiLocale);
    $uiTextEnd = $uiTextEnd ?? \Zarbin\Seo\Support\UiDirection::textAlignEnd($uiLocale);
    $hostMode = \Zarbin\Seo\Support\UiLayout::isHostMode();
    $layoutView = $hostMode ? \Zarbin\Seo\Support\UiLayout::hostView() : 'zarbin-seo::ui.standalone';
    $layoutView = $layoutView ?: 'zarbin-seo::ui.standalone';
    $layoutSection = $hostMode ? \Zarbin\Seo\Support\UiLayout::section() : 'zarbinSeoStandaloneContent';
    $layoutTitleSection = $hostMode ? \Zarbin\Seo\Support\UiLayout::titleSection() : 'title';
    $layoutData = \Zarbin\Seo\Support\UiLayout::data([
        'pageTitle' => $pageTitle,
        'uiLocale' => $uiLocale,
        'uiDirection' => $uiDirection,
        'uiDir' => $uiDir,
        'uiLang' => $uiLang,
        'uiIsRtl' => $uiIsRtl,
        'uiTextStart' => $uiTextStart,
        'uiTextEnd' => $uiTextEnd,
    ]);
@endphp

@extends($layoutView, $layoutData)

@section($layoutTitleSection, $pageTitle)

@section($layoutSection)
    @if($hostMode)
        @include('zarbin-seo::ui.partials.styles')
    @endif

    @include('zarbin-seo::ui.partials.shell')
@endsection
