<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Support\SeoFormFields;
use Zarbin\Seo\Tests\TestCase;

final class SeoFormFieldsTest extends TestCase
{
    public function test_fields_returns_expected_keys(): void
    {
        $this->assertSame([
            'title',
            'description',
            'canonical',
            'robots',
            'image',
            'og_title',
            'og_description',
            'og_image',
            'twitter_title',
            'twitter_description',
            'twitter_image',
            'schema_type',
            'extra',
        ], array_keys(SeoFormFields::fields()));
    }

    public function test_input_name_returns_seo_field_name(): void
    {
        $this->assertSame('seo[title]', SeoFormFields::inputName('title'));
    }

    public function test_values_prefer_override_over_resolved(): void
    {
        $values = SeoFormFields::values(
            ['title' => 'Override title', 'robots' => ['noindex', 'follow']],
            ['title' => 'Resolved title', 'canonical' => 'https://example.com']
        );

        $this->assertSame('Override title', $values['title']);
        $this->assertSame('https://example.com', $values['canonical']);
        $this->assertSame('noindex, follow', $values['robots']);
    }

    public function test_flatten_override_data_maps_and_normalizes_fields(): void
    {
        $attributes = SeoFormFields::flattenOverrideData([
            'seo' => [
                'title' => '0',
                'canonical' => 'https://example.com',
                'robots' => 'index, follow, index',
                'extra' => '{"score":0}',
                'unsupported' => 'drop',
            ],
        ]);

        $this->assertSame('0', $attributes['title']);
        $this->assertSame('https://example.com', $attributes['canonical_url']);
        $this->assertSame(['index', 'follow'], $attributes['robots']);
        $this->assertSame(['score' => 0], $attributes['extra']);
        $this->assertArrayNotHasKey('unsupported', $attributes);
    }

    public function test_flatten_override_data_ignores_invalid_extra_json(): void
    {
        $attributes = SeoFormFields::flattenOverrideData([
            'seo' => [
                'extra' => '{nope',
            ],
        ]);

        $this->assertArrayNotHasKey('extra', $attributes);
    }

    public function test_flatten_override_data_preserves_zero_values(): void
    {
        $attributes = SeoFormFields::flattenOverrideData([
            'seo' => [
                'description' => 0,
                'image' => '0',
            ],
        ]);

        $this->assertSame(0, $attributes['description']);
        $this->assertSame('0', $attributes['image']);
    }
}
