<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Support\UiLayout;
use Zarbin\Seo\Tests\TestCase;

final class UiLayoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        view()->addNamespace('testing', dirname(__DIR__).'/Fixtures/views');
    }

    public function test_default_mode_is_standalone(): void
    {
        $this->assertSame('standalone', UiLayout::mode());
        $this->assertFalse(UiLayout::isHostMode());
    }

    public function test_host_mode_with_view_returns_host_mode(): void
    {
        config()->set('zarbin-seo.ui.layout.mode', 'host');
        config()->set('zarbin-seo.ui.layout.view', 'testing::admin-layout');

        $this->assertSame('host', UiLayout::mode());
        $this->assertTrue(UiLayout::isHostMode());
        $this->assertSame('testing::admin-layout', UiLayout::hostView());
    }

    public function test_host_mode_without_view_falls_back_safely(): void
    {
        config()->set('zarbin-seo.ui.layout.mode', 'host');
        config()->set('zarbin-seo.ui.layout.view', null);

        $this->assertSame('standalone', UiLayout::mode());
        $this->assertFalse(UiLayout::isHostMode());
    }

    public function test_missing_host_view_falls_back_safely(): void
    {
        config()->set('zarbin-seo.ui.layout.mode', 'host');
        config()->set('zarbin-seo.ui.layout.view', 'testing::missing-layout');

        $this->assertSame('standalone', UiLayout::mode());
        $this->assertNull(UiLayout::hostView());
    }

    public function test_section_defaults_to_content(): void
    {
        config()->set('zarbin-seo.ui.layout.section', null);

        $this->assertSame('content', UiLayout::section());
    }

    public function test_data_includes_direction_keys(): void
    {
        $data = UiLayout::data([
            'pageTitle' => 'SEO Admin',
            'uiLocale' => 'fa',
        ]);

        $this->assertSame('rtl', $data['zarbinSeoDirection']);
        $this->assertSame('rtl', $data['zarbinSeoDir']);
        $this->assertSame('fa', $data['zarbinSeoLang']);
        $this->assertSame('SEO Admin', $data['zarbinSeoTitle']);
    }
}
