<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Zarbin\Seo\Support\SeoUiAuthorization;
use Zarbin\Seo\Tests\TestCase;

final class SeoUiAuthorizationTest extends TestCase
{
    public function test_no_gate_allows(): void
    {
        SeoUiAuthorization::authorize();

        $this->assertTrue(true);
    }

    public function test_configured_gate_allows_when_gate_allows(): void
    {
        $this->actingAs(new GenericUser(['id' => 1]));
        config()->set('zarbin-seo.ui.gate', 'viewZarbinSeo');
        Gate::define('viewZarbinSeo', fn (): bool => true);

        SeoUiAuthorization::authorize();

        $this->assertTrue(true);
    }

    public function test_configured_gate_aborts_when_denied(): void
    {
        $this->actingAs(new GenericUser(['id' => 1]));
        config()->set('zarbin-seo.ui.gate', 'viewZarbinSeo');
        Gate::define('viewZarbinSeo', fn (): bool => false);

        $this->expectException(HttpException::class);

        SeoUiAuthorization::authorize();
    }
}
