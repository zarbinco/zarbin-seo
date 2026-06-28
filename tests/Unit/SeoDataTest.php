<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zarbin\Seo\Data\SeoData;

final class SeoDataTest extends TestCase
{
    public function test_can_create_empty_seo_data(): void
    {
        $data = SeoData::make();

        $this->assertNull($data->title);
        $this->assertNull($data->description);
        $this->assertSame([], $data->robots);
        $this->assertSame('', $data->robotsContent());
    }

    public function test_can_create_from_array(): void
    {
        $data = SeoData::fromArray([
            'title' => 'About',
            'description' => 'About Zarbin',
            'canonical' => 'https://example.com/about',
            'robots' => 'index, follow',
            'image' => 'https://example.com/image.jpg',
            'type' => 'article',
            'locale' => 'en',
            'siteName' => 'Zarbin',
            'separator' => ' | ',
            'extra' => ['section' => 'company'],
        ]);

        $this->assertSame('About', $data->title);
        $this->assertSame('About Zarbin', $data->description);
        $this->assertSame('https://example.com/about', $data->canonical);
        $this->assertSame(['index', 'follow'], $data->robots);
        $this->assertSame('https://example.com/image.jpg', $data->image);
        $this->assertSame('article', $data->type);
        $this->assertSame('en', $data->locale);
        $this->assertSame('Zarbin', $data->siteName);
        $this->assertSame(' | ', $data->separator);
        $this->assertSame(['section' => 'company'], $data->extra);
    }

    public function test_robots_string_is_normalized(): void
    {
        $data = SeoData::make([
            'robots' => ' index, follow, , index ',
        ]);

        $this->assertSame(['index', 'follow'], $data->robots);
    }

    public function test_robots_array_is_normalized(): void
    {
        $data = SeoData::make([
            'robots' => ['index', ' follow ', '', 'index'],
        ]);

        $this->assertSame(['index', 'follow'], $data->robots);
    }

    public function test_merge_returns_a_new_object(): void
    {
        $data = SeoData::make(['title' => 'Original']);
        $merged = $data->merge(['description' => 'Merged']);

        $this->assertNotSame($data, $merged);
        $this->assertSame('Original', $merged->title);
        $this->assertSame('Merged', $merged->description);
        $this->assertNull($data->description);
    }

    public function test_with_title_returns_a_new_object(): void
    {
        $data = SeoData::make(['title' => 'Original']);
        $changed = $data->withTitle('Changed');

        $this->assertNotSame($data, $changed);
        $this->assertSame('Original', $data->title);
        $this->assertSame('Changed', $changed->title);
    }

    public function test_robots_content_returns_comma_separated_value(): void
    {
        $data = SeoData::make([
            'robots' => [' index ', 'follow'],
        ]);

        $this->assertSame('index, follow', $data->robotsContent());
    }
}
