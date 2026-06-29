<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Renderers\JsonLdRenderer;
use Zarbin\Seo\Tests\TestCase;

final class JsonLdProductRendererTest extends TestCase
{
    public function test_renders_product_json_ld_when_commerce_extra_exists(): void
    {
        $payload = $this->payload((new JsonLdRenderer)->render(SeoData::make([
            'title' => 'Product title',
            'extra' => [
                'commerce' => [
                    'sku' => 'SKU-1',
                ],
            ],
        ])));

        $this->assertSame('Product', $payload['@type']);
        $this->assertSame('Product title', $payload['name']);
        $this->assertSame('SKU-1', $payload['sku']);
    }

    public function test_renders_offer_json_ld(): void
    {
        $payload = $this->payload((new JsonLdRenderer)->render(SeoData::make([
            'title' => 'Product title',
            'extra' => [
                'commerce' => [
                    'price' => 120000,
                    'currency' => 'IRR',
                    'availability' => 'in_stock',
                ],
            ],
        ])));

        $this->assertSame('Offer', $payload['offers']['@type']);
        $this->assertSame(120000, $payload['offers']['price']);
        $this->assertSame('IRR', $payload['offers']['priceCurrency']);
    }

    public function test_respects_schema_feature_disabled(): void
    {
        config()->set('zarbin-seo.features.schema', false);

        $html = (new JsonLdRenderer)->render(SeoData::make([
            'title' => 'Product title',
            'extra' => ['commerce' => ['price' => 1]],
        ]));

        $this->assertSame('', $html);
    }

    public function test_falls_back_to_basic_web_page_json_ld(): void
    {
        $payload = $this->payload((new JsonLdRenderer)->render(SeoData::make([
            'title' => 'About',
        ])));

        $this->assertSame('WebPage', $payload['@type']);
    }

    public function test_keeps_persian_unicode_unescaped(): void
    {
        $html = (new JsonLdRenderer)->render(SeoData::make([
            'title' => 'محصول',
            'extra' => [
                'commerce' => ['price' => 1],
            ],
        ]));

        $this->assertStringContainsString('محصول', $html);
        $this->assertStringNotContainsString('\u0645', $html);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(string $html): array
    {
        preg_match('/<script[^>]*>(.*)<\/script>/s', $html, $matches);

        return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
    }
}
