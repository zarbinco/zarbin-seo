@extends('zarbin-seo::ui.layout', ['title' => \Zarbin\Seo\Support\UiTranslator::get('models.title'), 'routeNamePrefix' => $routeNamePrefix])

@section('content')
    <x-zarbin-seo::models :locale="$uiLocale ?? null" />
@endsection
