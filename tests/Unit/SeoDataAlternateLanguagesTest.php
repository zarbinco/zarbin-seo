<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zarbin\Seo\Data\SeoData;

final class SeoDataAlternateLanguagesTest extends TestCase
{
    public function test_from_array_supports_alternate_languages(): void
    {
        $data = SeoData::fromArray([
            'alternate_languages' => ['fa' => 'https://example.com/fa'],
        ]);

        $this->assertSame(['fa' => 'https://example.com/fa'], $data->alternateLanguages);
        $this->assertSame(['fa' => 'https://example.com/fa'], $data->toArray()['alternate_languages']);
    }

    public function test_from_array_supports_alternate_languages_camel_case(): void
    {
        $data = SeoData::fromArray([
            'alternateLanguages' => ['en' => 'https://example.com/en'],
        ]);

        $this->assertSame(['en' => 'https://example.com/en'], $data->alternateLanguages);
    }

    public function test_with_alternate_languages_normalizes_values(): void
    {
        $data = SeoData::make()->withAlternateLanguages([
            ' fa ' => ' https://example.com/fa ',
            '' => 'https://example.com/empty',
            'en' => '',
        ]);

        $this->assertSame(['fa' => 'https://example.com/fa'], $data->alternateLanguages);
        $this->assertTrue($data->hasAlternateLanguages());
    }

    public function test_add_alternate_language_works(): void
    {
        $data = SeoData::make()->addAlternateLanguage('fa', 'https://example.com/fa');

        $this->assertSame(['fa' => 'https://example.com/fa'], $data->alternateLanguages);
    }

    public function test_merge_replaces_duplicate_locale_keys(): void
    {
        $data = SeoData::make([
            'alternate_languages' => ['fa' => 'https://example.com/fa-old'],
        ])->merge([
            'alternate_languages' => ['fa' => 'https://example.com/fa-new', 'en' => 'https://example.com/en'],
        ]);

        $this->assertSame([
            'fa' => 'https://example.com/fa-new',
            'en' => 'https://example.com/en',
        ], $data->alternateLanguages);
    }
}
