<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Support\XmlResponse;
use Zarbin\Seo\Tests\TestCase;

final class XmlResponseTest extends TestCase
{
    public function test_default_content_type_is_application_xml(): void
    {
        $response = XmlResponse::make('<root />');

        $this->assertSame('application/xml; charset=UTF-8', $response->headers->get('Content-Type'));
    }

    public function test_configured_text_xml_content_type_is_respected(): void
    {
        config()->set('zarbin-seo.sitemap.content_type', 'text/xml; charset=UTF-8');

        $response = XmlResponse::make('<root />');

        $this->assertSame('text/xml; charset=UTF-8', $response->headers->get('Content-Type'));
    }

    public function test_empty_or_invalid_content_type_falls_back_to_application_xml(): void
    {
        foreach (['', 'not-a-content-type', []] as $contentType) {
            config()->set('zarbin-seo.sitemap.content_type', $contentType);

            $response = XmlResponse::make('<root />');

            $this->assertSame('application/xml; charset=UTF-8', $response->headers->get('Content-Type'));
        }
    }

    public function test_sets_nosniff_header_and_preserves_xml_content(): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><root><node /></root>';

        $response = XmlResponse::make($xml);

        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame($xml, $response->getContent());
    }
}
