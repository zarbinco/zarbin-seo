@extends('zarbin-seo::ui.layout', ['title' => \Zarbin\Seo\Support\UiTranslator::get('routes.edit_title'), 'routeNamePrefix' => $routeNamePrefix])

@section('content')
    <x-zarbin-seo::route-form :route="$routeName" :locale="$locale ?? null" />
@endsection
