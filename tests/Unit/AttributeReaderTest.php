<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use Zarbin\Seo\Support\AttributeReader;

final class AttributeReaderTest extends TestCase
{
    public function test_reads_array_values(): void
    {
        $this->assertSame('About', AttributeReader::get(['title' => 'About'], 'title'));
    }

    public function test_reads_array_access_values(): void
    {
        $this->assertSame('About', AttributeReader::get(new ArrayObject(['title' => 'About']), 'title'));
    }

    public function test_reads_object_public_properties(): void
    {
        $source = new class
        {
            public string $title = 'Public title';
        };

        $this->assertSame('Public title', AttributeReader::get($source, 'title'));
    }

    public function test_reads_zero_argument_methods(): void
    {
        $source = new class
        {
            public function title(): string
            {
                return 'Method title';
            }
        };

        $this->assertSame('Method title', AttributeReader::get($source, 'title'));
    }

    public function test_reads_nested_dot_paths(): void
    {
        $source = [
            'author' => (object) [
                'profile' => [
                    'name' => 'Ava',
                ],
            ],
        ];

        $this->assertSame('Ava', AttributeReader::get($source, 'author.profile.name'));
    }

    public function test_returns_first_non_empty_value(): void
    {
        $source = [
            'title' => '',
            'name' => 'Product',
        ];

        $this->assertSame('Product', AttributeReader::first($source, ['title', 'name']));
    }

    public function test_does_not_treat_zero_values_as_empty(): void
    {
        $this->assertSame(0, AttributeReader::first(['title' => 0, 'name' => 'Fallback'], ['title', 'name']));
        $this->assertSame('0', AttributeReader::first(['title' => '0', 'name' => 'Fallback'], ['title', 'name']));
    }

    public function test_returns_default_for_missing_values(): void
    {
        $this->assertSame('Default', AttributeReader::get([], 'missing', 'Default'));
        $this->assertFalse(AttributeReader::exists([], 'missing'));
    }
}
