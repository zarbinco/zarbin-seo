@extends('zarbin-seo::ui.layout', ['title' => \Zarbin\Seo\Support\UiTranslator::get('models.edit_title'), 'routeNamePrefix' => $routeNamePrefix])

@section('content')
    <x-zarbin-seo::model-form :source="$model ?? null" :model="$modelToken ?? null" :id="$modelKey ?? null" :locale="$locale ?? null" />
@endsection
