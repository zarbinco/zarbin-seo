<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Data\SearchPreviewData;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Tests\TestCase;

final class SearchPreviewDataTest extends TestCase
{
    public function test_makes_from_array(): void
    {
        $preview = SearchPreviewData::make([
            'title' => 'Title',
            'url' => 'https://example.test/page',
            'description' => 'Description',
            'locale' => 'fa',
            'warnings' => ['missing_title', 'missing_title'],
        ]);

        $this->assertSame('Title', $preview->title);
        $this->assertSame('https://example.test/page', $preview->url);
        $this->assertSame('Description', $preview->description);
        $this->assertSame('fa', $preview->locale);
        $this->assertSame(['missing_title'], $preview->warnings);
    }

    public function test_makes_from_seo_data(): void
    {
        $preview = SearchPreviewData::fromSeoData(SeoData::make([
            'title' => 'SEO title',
            'canonical' => 'https://example.test/page',
            'description' => 'Meta description',
            'locale' => 'en',
        ]));

        $this->assertTrue($preview->hasTitle());
        $this->assertTrue($preview->hasUrl());
        $this->assertTrue($preview->hasDescription());
        $this->assertSame('SEO title', $preview->toArray()['title']);
    }

    public function test_empty_values_are_reported(): void
    {
        $preview = SearchPreviewData::make();

        $this->assertFalse($preview->hasTitle());
        $this->assertFalse($preview->hasUrl());
        $this->assertFalse($preview->hasDescription());
    }
}
