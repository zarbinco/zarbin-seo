@extends('zarbin-seo::ui.layout', ['title' => \Zarbin\Seo\Support\UiTranslator::get('routes.title'), 'routeNamePrefix' => $routeNamePrefix])

@section('content')
    <x-zarbin-seo::routes :locale="$uiLocale ?? null" />
@endsection
