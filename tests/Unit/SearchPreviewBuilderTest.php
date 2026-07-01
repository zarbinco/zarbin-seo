<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\SearchPreviewBuilder;
use Zarbin\Seo\Tests\TestCase;

final class SearchPreviewBuilderTest extends TestCase
{
    public function test_builds_title_url_and_description_from_seo_data(): void
    {
        $preview = $this->builder()->build(SeoData::make([
            'title' => 'SEO title',
            'canonical' => 'https://example.test/page',
            'description' => 'Meta description',
            'locale' => 'fa',
        ]));

        $this->assertSame('SEO title', $preview->title);
        $this->assertSame('https://example.test/page', $preview->url);
        $this->assertSame('Meta description', $preview->description);
        $this->assertSame('fa', $preview->locale);
    }

    public function test_falls_back_to_site_name_when_title_missing(): void
    {
        $preview = $this->builder()->build(SeoData::make([
            'siteName' => 'Example',
            'canonical' => 'https://example.test',
            'description' => 'Description',
        ]));

        $this->assertSame('Example', $preview->title);
        $this->assertNotContains('missing_title', $preview->warnings);
    }

    public function test_warns_for_missing_title(): void
    {
        $preview = $this->builder()->build(SeoData::make([
            'canonical' => 'https://example.test',
            'description' => 'Description',
        ]));

        $this->assertContains('missing_title', $preview->warnings);
    }

    public function test_warns_for_missing_url(): void
    {
        $preview = $this->builder()->build(SeoData::make([
            'title' => 'Title',
            'description' => 'Description',
        ]));

        $this->assertContains('missing_url', $preview->warnings);
    }

    public function test_warns_for_missing_description(): void
    {
        $preview = $this->builder()->build(SeoData::make([
            'title' => 'Title',
            'canonical' => 'https://example.test',
        ]));

        $this->assertContains('missing_description', $preview->warnings);
    }

    public function test_warns_for_long_title(): void
    {
        config()->set('zarbin-seo.ui.preview.title_limit', 10);

        $preview = $this->builder()->build(SeoData::make([
            'title' => 'A title that is too long',
            'canonical' => 'https://example.test',
            'description' => 'Description',
        ]));

        $this->assertContains('long_title', $preview->warnings);
    }

    public function test_warns_for_long_description(): void
    {
        config()->set('zarbin-seo.ui.preview.description_limit', 10);

        $preview = $this->builder()->build(SeoData::make([
            'title' => 'Title',
            'canonical' => 'https://example.test',
            'description' => 'A description that is too long',
        ]));

        $this->assertContains('long_description', $preview->warnings);
    }

    public function test_preserves_persian_text(): void
    {
        $preview = $this->builder()->build(SeoData::make([
            'title' => 'محصول سن‌ایچ',
            'canonical' => 'https://example.test/fa/products',
            'description' => 'توضیحات فارسی محصول',
        ]));

        $this->assertSame('محصول سن‌ایچ', $preview->title);
        $this->assertSame('توضیحات فارسی محصول', $preview->description);
    }

    private function builder(): SearchPreviewBuilder
    {
        return new SearchPreviewBuilder;
    }
}
