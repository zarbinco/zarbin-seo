<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature\Bulletproof;

use Illuminate\Support\Facades\Blade;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Data\SitemapUrl;
use Zarbin\Seo\Renderers\JsonLdRenderer;
use Zarbin\Seo\Renderers\OpenGraphRenderer;
use Zarbin\Seo\Renderers\SitemapRenderer;
use Zarbin\Seo\Renderers\TitleRenderer;
use Zarbin\Seo\Renderers\TwitterCardRenderer;
use Zarbin\Seo\Tests\TestCase;

final class RenderingSafetyTest extends TestCase
{
    public function test_html_json_ld_and_sitemap_escape_unsafe_values(): void
    {
        $data = SeoData::make([
            'title' => '<script>alert(1)</script>',
            'description' => '<b>Description</b> & more',
            'canonical' => 'https://example.test/?a=1&b=<x>',
            'image' => 'https://example.test/<image>.jpg',
            'siteName' => 'Example',
        ]);

        $html = seo()->set($data)->render();
        $xml = (new SitemapRenderer)->render([
            SitemapUrl::make(['loc' => 'https://example.test/?a=1&b=<x>']),
        ]);

        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('\\u003Cscript\\u003Ealert(1)\\u003C/script\\u003E', $html);
        $this->assertStringContainsString('https://example.test/?a=1&amp;b=&lt;x&gt;', $xml);
    }

    public function test_empty_title_and_site_name_render_empty_title_tag(): void
    {
        $this->assertSame('', (new TitleRenderer)->render(SeoData::make()));
    }

    public function test_robot_values_normalize_safely(): void
    {
        $data = SeoData::make(['robots' => ['', ' index ', 'follow', 'index']]);

        $this->assertSame('index, follow', $data->robotsContent());
        $this->assertStringContainsString('name="robots"', seo()->set($data)->render());
    }

    public function test_unexpected_nested_extra_values_do_not_crash_renderers(): void
    {
        $data = SeoData::make([
            'title' => 'Safe title',
            'extra' => [
                'og_title' => ['not scalar'],
                'open_graph' => 'not an array',
                'twitter_title' => ['not scalar'],
                'twitter' => new \stdClass,
                'commerce' => 'not an array',
            ],
        ]);

        $this->assertStringContainsString('og:title', (new OpenGraphRenderer)->render($data));
        $this->assertStringContainsString('twitter:title', (new TwitterCardRenderer)->render($data));
        $this->assertStringContainsString('"@type":"WebPage"', (new JsonLdRenderer)->render($data));
    }

    public function test_blade_meta_component_handles_null_array_and_scalar_sources(): void
    {
        seo()->reset()->title('Current');

        $nullHtml = Blade::render('<x-zarbin-seo::meta />');
        $arrayHtml = Blade::render('<x-zarbin-seo::meta :source="$source" />', [
            'source' => ['title' => 'Array source'],
        ]);
        $scalarHtml = Blade::render('<x-zarbin-seo::meta :source="$source" />', [
            'source' => 123,
        ]);

        $this->assertStringContainsString('Current', $nullHtml);
        $this->assertStringContainsString('Array source', $arrayHtml);
        $this->assertIsString($scalarHtml);
    }

    public function test_blade_form_component_warns_when_database_is_missing(): void
    {
        $html = Blade::render('<x-zarbin-seo::form :source="$source" />', [
            'source' => new RenderingSafetyModel,
        ]);

        $this->assertStringContainsString('SEO database overrides are not ready', $html);
        $this->assertStringContainsString('name="seo[title]"', $html);
    }
}

final class RenderingSafetyModel
{
    public string $title = 'Rendering model';
}
