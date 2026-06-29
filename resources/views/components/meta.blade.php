@props([
    'source' => null,
    'locale' => null,
    'minify' => false,
])

{!! $html ?? ($source === null
    ? seo()->render((bool) $minify)
    : seo()->renderer()->render(seo()->resolve($source, $locale), (bool) $minify)) !!}
