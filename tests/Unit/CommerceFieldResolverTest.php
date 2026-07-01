<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use stdClass;
use Zarbin\Seo\Support\CommerceFieldResolver;
use Zarbin\Seo\Tests\TestCase;

final class CommerceFieldResolverTest extends TestCase
{
    public function test_resolves_simple_dot_path(): void
    {
        $source = ['brand' => ['name' => 'Sunich']];

        $this->assertSame('Sunich', $this->resolver()->resolve($source, 'brand.name'));
    }

    public function test_resolves_nested_relation_path(): void
    {
        $source = (object) ['discount' => (object) ['price' => 1200]];

        $this->assertSame(1200, $this->resolver()->resolve($source, 'discount.price'));
    }

    public function test_resolves_locale_placeholder_path(): void
    {
        $source = [
            'translations' => [
                'fa' => ['price' => 1200],
                'en' => ['price' => 2],
            ],
        ];

        $this->assertSame(1200, $this->resolver()->resolve($source, 'translations.{locale}.price', 'fa'));
    }

    public function test_resolves_collection_filter_path(): void
    {
        $source = [
            'translations' => [
                (object) ['locale' => 'en', 'price' => 2],
                (object) ['locale' => 'fa', 'price' => 1200],
            ],
        ];

        $this->assertSame(1200, $this->resolver()->resolve($source, 'translations[locale={locale}].price', 'fa'));
    }

    public function test_resolves_relation_where_value_mapping(): void
    {
        $source = (object) [
            'prices' => [
                ['currency' => 'USD', 'amount' => 2],
                ['currency' => 'IRR', 'amount' => 1200],
            ],
        ];

        $value = $this->resolver()->resolve($source, [
            'relation' => 'prices',
            'where' => ['currency' => 'IRR'],
            'value' => 'amount',
        ]);

        $this->assertSame(1200, $value);
    }

    public function test_resolves_callable_with_source_and_locale(): void
    {
        $source = (object) ['prices' => ['fa' => 1200]];

        $value = $this->resolver()->resolve(
            $source,
            static fn (object $source, ?string $locale): int => $source->prices[$locale ?? 'fa'],
            'fa',
        );

        $this->assertSame(1200, $value);
    }

    public function test_resolves_method_with_locale_parameter(): void
    {
        $source = new CommerceFieldResolverProduct;

        $value = $this->resolver()->resolve($source, [
            'method' => 'priceForLocale',
            'parameters' => ['{locale}'],
        ], 'fa');

        $this->assertSame(1200, $value);
    }

    public function test_resolves_literal_mapping(): void
    {
        $this->assertSame('IRR', $this->resolver()->resolve(new stdClass, 'literal:IRR'));
        $this->assertSame('IRR', $this->resolver()->resolve(new stdClass, ['literal' => 'IRR']));
    }

    public function test_fallback_array_returns_first_non_empty_value(): void
    {
        $source = ['first' => '', 'second' => 0, 'third' => 1200];

        $this->assertSame(0, $this->resolver()->resolve($source, ['first', 'second', 'third']));
    }

    public function test_preserves_zero_string(): void
    {
        $source = ['price' => '0'];

        $this->assertSame('0', $this->resolver()->resolve($source, 'price'));
    }

    public function test_malformed_mapping_does_not_throw(): void
    {
        $this->assertNull($this->resolver()->resolve(new stdClass, ['relation' => ['broken']]));
        $this->assertNull($this->resolver()->resolve(new stdClass, ['method' => 'missing']));
        $this->assertNull($this->resolver()->resolve(new stdClass, 'translations[bad'));
    }

    private function resolver(): CommerceFieldResolver
    {
        return new CommerceFieldResolver;
    }
}

final class CommerceFieldResolverProduct
{
    public function priceForLocale(string $locale): int
    {
        return $locale === 'fa' ? 1200 : 2;
    }
}
