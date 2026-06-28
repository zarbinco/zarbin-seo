<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zarbin\Seo\Concerns\HasSeo;
use Zarbin\Seo\Contracts\Seoable;
use Zarbin\Seo\Data\SeoData;

final class HasSeoTest extends TestCase
{
    public function test_default_trait_returns_seo_data(): void
    {
        $model = new class implements Seoable
        {
            use HasSeo;
        };

        $this->assertInstanceOf(SeoData::class, $model->toSeoData());
    }

    public function test_overriding_title_and_description_works(): void
    {
        $model = new class implements Seoable
        {
            use HasSeo;

            public function seoTitle(?string $locale = null): ?string
            {
                return 'Custom title';
            }

            public function seoDescription(?string $locale = null): ?string
            {
                return 'Custom description';
            }
        };

        $data = $model->toSeoData();

        $this->assertSame('Custom title', $data->title);
        $this->assertSame('Custom description', $data->description);
    }

    public function test_passing_locale_works(): void
    {
        $model = new class implements Seoable
        {
            use HasSeo;

            public function seoTitle(?string $locale = null): ?string
            {
                return $locale === 'fa' ? 'Localized title' : 'Title';
            }
        };

        $data = $model->toSeoData('fa');

        $this->assertSame('fa', $data->locale);
        $this->assertSame('Localized title', $data->title);
    }
}
