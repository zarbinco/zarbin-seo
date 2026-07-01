<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Support\UiDirection;
use Zarbin\Seo\Tests\TestCase;

final class UiDirectionTest extends TestCase
{
    public function test_fa_is_rtl(): void
    {
        $this->assertSame('rtl', UiDirection::current('fa'));
    }

    public function test_ar_is_rtl(): void
    {
        $this->assertSame('rtl', UiDirection::current('ar'));
    }

    public function test_region_locales_are_normalized(): void
    {
        $this->assertSame('rtl', UiDirection::current('fa_IR'));
        $this->assertSame('rtl', UiDirection::current('ar-SA'));
    }

    public function test_en_is_ltr(): void
    {
        $this->assertSame('ltr', UiDirection::current('en'));
    }

    public function test_forced_rtl_mode_returns_rtl(): void
    {
        config()->set('zarbin-seo.ui.direction.mode', 'rtl');

        $this->assertSame('rtl', UiDirection::current('en'));
    }

    public function test_forced_ltr_mode_returns_ltr(): void
    {
        config()->set('zarbin-seo.ui.direction.mode', 'ltr');

        $this->assertSame('ltr', UiDirection::current('fa'));
    }

    public function test_malformed_mode_falls_back_safely(): void
    {
        config()->set('zarbin-seo.ui.direction.mode', 'sideways');

        $this->assertSame('rtl', UiDirection::current('fa'));
        $this->assertSame('ltr', UiDirection::current('en'));
    }

    public function test_custom_rtl_locales_work(): void
    {
        config()->set('zarbin-seo.ui.direction.rtl_locales', ['dv']);

        $this->assertSame('rtl', UiDirection::current('dv'));
        $this->assertSame('ltr', UiDirection::current('fa'));
    }

    public function test_text_align_helpers_work(): void
    {
        $this->assertSame('right', UiDirection::textAlignStart('fa'));
        $this->assertSame('left', UiDirection::textAlignEnd('fa'));
        $this->assertSame('left', UiDirection::textAlignStart('en'));
        $this->assertSame('right', UiDirection::textAlignEnd('en'));
    }

    public function test_html_attributes_include_direction_and_lang(): void
    {
        $this->assertSame(['dir' => 'rtl', 'lang' => 'fa-IR'], UiDirection::htmlAttributes('fa_IR'));
    }
}
