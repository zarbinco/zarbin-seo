<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Contracts\LocalizableSeo;
use Zarbin\Seo\Resolvers\TranslationAvailabilityResolver;
use Zarbin\Seo\Tests\TestCase;

final class TranslationAvailabilityResolverTest extends TestCase
{
    public function test_localizable_seo_contract_controls_availability(): void
    {
        $source = new AvailabilityLocalizableSource(['fa']);

        $this->assertTrue($this->resolver()->isAvailable($source, 'fa'));
        $this->assertFalse($this->resolver()->isAvailable($source, 'en'));
    }

    public function test_has_seo_locale_method_controls_availability(): void
    {
        $source = new AvailabilityHasSeoLocaleSource(['en']);

        $this->assertTrue($this->resolver()->isAvailable($source, 'en'));
        $this->assertFalse($this->resolver()->isAvailable($source, 'fa'));
    }

    public function test_is_seo_available_for_locale_method_controls_availability(): void
    {
        $source = new AvailabilityIsSeoAvailableSource(['fa']);

        $this->assertTrue($this->resolver()->isAvailable($source, 'fa'));
        $this->assertFalse($this->resolver()->isAvailable($source, 'en'));
    }

    public function test_should_show_locale_method_controls_availability(): void
    {
        $source = new AvailabilityShouldShowSource(['fa']);

        $this->assertTrue($this->resolver()->isAvailable($source, 'fa'));
        $this->assertFalse($this->resolver()->isAvailable($source, 'en'));
    }

    public function test_config_translation_availability_with_get_translation_works(): void
    {
        config()->set('zarbin-seo.models.'.AvailabilityGetTranslationSource::class.'.translation_availability', ['title']);

        $source = new AvailabilityGetTranslationSource([
            'title' => ['fa' => 'عنوان'],
        ]);

        $this->assertTrue($this->resolver()->isAvailable($source, 'fa'));
        $this->assertFalse($this->resolver()->isAvailable($source, 'en'));
    }

    public function test_config_translation_availability_with_nested_arrays_works(): void
    {
        config()->set('zarbin-seo.models.'.AvailabilityNestedSource::class.'.translation_availability', ['title']);

        $source = new AvailabilityNestedSource;
        $source->title = ['fa' => 'عنوان'];

        $this->assertTrue($this->resolver()->isAvailable($source, 'fa'));
        $this->assertFalse($this->resolver()->isAvailable($source, 'en'));
    }

    public function test_zero_values_are_available(): void
    {
        config()->set('zarbin-seo.models.'.AvailabilityNestedSource::class.'.translation_availability', ['title']);

        $integer = new AvailabilityNestedSource;
        $integer->title = ['fa' => 0];

        $string = new AvailabilityNestedSource;
        $string->title = ['fa' => '0'];

        $this->assertTrue($this->resolver()->isAvailable($integer, 'fa'));
        $this->assertTrue($this->resolver()->isAvailable($string, 'fa'));
    }

    public function test_empty_values_are_unavailable(): void
    {
        config()->set('zarbin-seo.models.'.AvailabilityNestedSource::class.'.translation_availability', ['title']);

        $source = new AvailabilityNestedSource;
        $source->title = ['fa' => ''];

        $this->assertFalse($this->resolver()->isAvailable($source, 'fa'));
    }

    public function test_default_for_normal_object_is_available(): void
    {
        $this->assertTrue($this->resolver()->isAvailable(new \stdClass, 'fa'));
    }

    private function resolver(): TranslationAvailabilityResolver
    {
        return new TranslationAvailabilityResolver;
    }
}

final class AvailabilityLocalizableSource implements LocalizableSeo
{
    /**
     * @param  array<int, string>  $locales
     */
    public function __construct(private readonly array $locales) {}

    public function seoLocales(): array
    {
        return $this->locales;
    }

    public function hasSeoLocale(string $locale): bool
    {
        return in_array($locale, $this->locales, true);
    }

    public function seoUrlForLocale(string $locale): ?string
    {
        return null;
    }
}

final class AvailabilityHasSeoLocaleSource
{
    /**
     * @param  array<int, string>  $locales
     */
    public function __construct(private readonly array $locales) {}

    public function hasSeoLocale(string $locale): bool
    {
        return in_array($locale, $this->locales, true);
    }
}

final class AvailabilityIsSeoAvailableSource
{
    /**
     * @param  array<int, string>  $locales
     */
    public function __construct(private readonly array $locales) {}

    public function isSeoAvailableForLocale(string $locale): bool
    {
        return in_array($locale, $this->locales, true);
    }
}

final class AvailabilityShouldShowSource
{
    /**
     * @param  array<int, string>  $locales
     */
    public function __construct(private readonly array $locales) {}

    public function shouldShowLocale(string $locale): bool
    {
        return in_array($locale, $this->locales, true);
    }
}

final class AvailabilityGetTranslationSource
{
    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    public function __construct(private readonly array $translations) {}

    public function getTranslation(string $field, string $locale): mixed
    {
        return $this->translations[$field][$locale] ?? null;
    }
}

final class AvailabilityNestedSource
{
    /**
     * @var array<string, mixed>
     */
    public array $title = [];
}
