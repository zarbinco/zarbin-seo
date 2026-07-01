<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Zarbin SEO' }}</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; color: #1f2937; background: #f8fafc; }
        header, main { max-width: 1040px; margin: 0 auto; padding: 24px; }
        header { display: flex; align-items: center; justify-content: space-between; gap: 16px; border-bottom: 1px solid #e5e7eb; background: #fff; }
        nav a { margin-left: 12px; color: #2563eb; text-decoration: none; }
        section, .zarbin-seo-panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
        label { display: block; font-weight: 600; margin-bottom: 6px; }
        input, textarea, select { width: 100%; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 6px; padding: 9px 10px; font: inherit; }
        textarea { min-height: 90px; resize: vertical; }
        .zarbin-seo-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; }
        .zarbin-seo-field { margin-bottom: 16px; }
        .zarbin-seo-help { color: #6b7280; font-size: 0.9rem; margin-top: 4px; }
        .zarbin-seo-status { font-weight: 700; }
        .zarbin-seo-status-complete { color: #166534; }
        .zarbin-seo-status-incomplete { color: #991b1b; }
        .zarbin-seo-actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        button, .zarbin-seo-button { border: 0; border-radius: 6px; background: #111827; color: #fff; padding: 9px 13px; font: inherit; cursor: pointer; text-decoration: none; display: inline-block; }
        button[disabled] { opacity: .5; cursor: not-allowed; }
        .zarbin-seo-button-secondary { background: #e5e7eb; color: #111827; }
        .zarbin-seo-alert { border-radius: 6px; padding: 12px 14px; margin-bottom: 16px; background: #fef3c7; color: #92400e; }
        .zarbin-seo-alert-success { background: #dcfce7; color: #166534; }
        .zarbin-seo-alert-error { background: #fee2e2; color: #991b1b; }
        .zarbin-seo-preview { font-family: ui-monospace, SFMono-Regular, Consolas, monospace; min-height: 180px; }
    </style>
</head>
<body>
@php($prefix = $routeNamePrefix ?? \Zarbin\Seo\Support\UiConfig::routeNamePrefix())
<header>
    <div>
        <strong>Zarbin SEO</strong>
    </div>
    <nav aria-label="SEO navigation">
        <a href="{{ route($prefix.'dashboard') }}">Dashboard</a>
        <a href="{{ route($prefix.'routes.index') }}">Routes</a>
    </nav>
</header>
<main>
    @include('zarbin-seo::components.alert')
    @yield('content')
</main>
</body>
</html>
