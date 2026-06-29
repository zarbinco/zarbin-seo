<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Models\SeoMeta;
use Zarbin\Seo\Tests\TestCase;

final class SeoMetaModelTest extends TestCase
{
    public function test_normalized_locale_returns_empty_string_for_null_or_empty(): void
    {
        $this->assertSame('', SeoMeta::normalizedLocale());
        $this->assertSame('', SeoMeta::normalizedLocale('   '));
        $this->assertSame('fa', SeoMeta::normalizedLocale(' fa '));
    }

    public function test_type_for_source_uses_morph_class_when_available(): void
    {
        $this->assertSame('posts', SeoMeta::typeForSource(new SeoMetaMorphSource));
    }

    public function test_id_for_source_uses_get_key_when_available(): void
    {
        $this->assertSame('123', SeoMeta::idForSource(new SeoMetaKeySource(123)));
    }

    public function test_id_for_source_supports_string_ids(): void
    {
        $this->assertSame('post-uuid', SeoMeta::idForSource(new SeoMetaKeySource('post-uuid')));
        $this->assertSame('public-id', SeoMeta::idForSource(new SeoMetaPublicIdSource));
    }

    public function test_route_type_returns_configured_route_type(): void
    {
        config()->set('zarbin-seo.database.route_type', 'named-route');

        $this->assertSame('named-route', SeoMeta::routeType());
    }

    public function test_to_seo_data_maps_core_fields(): void
    {
        $data = (new SeoMeta([
            'title' => 'Database title',
            'description' => 'Database description',
            'canonical_url' => 'https://example.com/database',
            'robots' => ['noindex', 'follow'],
            'image' => 'https://example.com/image.jpg',
            'schema_type' => 'Article',
        ]))->toSeoData();

        $this->assertSame('Database title', $data->title);
        $this->assertSame('Database description', $data->description);
        $this->assertSame('https://example.com/database', $data->canonical);
        $this->assertSame(['noindex', 'follow'], $data->robots);
        $this->assertSame('https://example.com/image.jpg', $data->image);
        $this->assertSame('Article', $data->type);
    }

    public function test_to_seo_data_maps_social_fields_into_extra(): void
    {
        $data = (new SeoMeta([
            'og_title' => 'OG title',
            'og_description' => 'OG description',
            'og_image' => 'https://example.com/og.jpg',
            'twitter_title' => 'Twitter title',
            'twitter_description' => 'Twitter description',
            'twitter_image' => 'https://example.com/twitter.jpg',
        ]))->toSeoData();

        $this->assertSame('OG title', $data->extra['og_title']);
        $this->assertSame('OG description', $data->extra['og_description']);
        $this->assertSame('https://example.com/og.jpg', $data->extra['og_image']);
        $this->assertSame('Twitter title', $data->extra['twitter_title']);
        $this->assertSame('Twitter description', $data->extra['twitter_description']);
        $this->assertSame('https://example.com/twitter.jpg', $data->extra['twitter_image']);
    }

    public function test_to_seo_data_merges_extra_json(): void
    {
        $data = (new SeoMeta([
            'extra' => ['editor_note' => 'Manual review'],
            'og_title' => 'Column OG title',
        ]))->toSeoData();

        $this->assertSame('Manual review', $data->extra['editor_note']);
        $this->assertSame('Column OG title', $data->extra['og_title']);
    }
}

final class SeoMetaMorphSource
{
    public function getMorphClass(): string
    {
        return 'posts';
    }
}

final class SeoMetaKeySource
{
    public function __construct(private readonly int|string $key) {}

    public function getKey(): int|string
    {
        return $this->key;
    }
}

final class SeoMetaPublicIdSource
{
    public string $id = 'public-id';
}
