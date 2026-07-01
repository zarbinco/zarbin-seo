<!doctype html>
<html lang="{{ $zarbinSeoLang ?? 'en' }}" dir="{{ $zarbinSeoDir ?? 'ltr' }}">
<body>
<div id="host-layout">
    <h1 id="host-title">@yield('title')</h1>
    <span id="host-data">{{ $zarbinSeoTitle ?? '' }}|{{ $zarbinSeoDir ?? '' }}|{{ $zarbinSeoLang ?? '' }}</span>
    <div id="host-content">
        @yield('content')
    </div>
</div>
</body>
</html>
