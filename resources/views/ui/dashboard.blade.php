@extends('zarbin-seo::ui.layout', ['title' => \Zarbin\Seo\Support\UiTranslator::get('dashboard.title'), 'routeNamePrefix' => $routeNamePrefix])

@section('content')
    <x-zarbin-seo::dashboard :locale="$uiLocale ?? null" />
@endsection
