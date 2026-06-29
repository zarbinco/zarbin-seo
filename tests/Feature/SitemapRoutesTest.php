<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Tests\TestCase;

final class SitemapRoutesTest extends TestCase
{
    public function test_sitemap_route_returns_xml_response(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
    }

    public function test_sitemap_index_route_returns_xml_response(): void
    {
        $response = $this->get('/sitemap_index.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));
        $response->assertSee('<sitemapindex', false);
    }

    public function test_robots_txt_route_returns_plain_text_response(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $this->assertStringContainsString('text/plain', (string) $response->headers->get('Content-Type'));
        $response->assertSee('User-agent: *', false);
    }

    public function test_route_names_exist(): void
    {
        $this->assertTrue(Route::has('zarbin-seo.sitemap'));
        $this->assertTrue(Route::has('zarbin-seo.sitemap.index'));
        $this->assertTrue(Route::has('zarbin-seo.robots'));
    }
}
