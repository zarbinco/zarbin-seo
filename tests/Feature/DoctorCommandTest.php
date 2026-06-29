<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Zarbin\Seo\Tests\TestCase;

final class DoctorCommandTest extends TestCase
{
    public function test_doctor_returns_success_in_normal_config(): void
    {
        $this->artisan('zarbin-seo:doctor')
            ->expectsOutputToContain('Service binding')
            ->assertExitCode(0);
    }

    public function test_json_option_outputs_valid_json(): void
    {
        $this->artisan('zarbin-seo:doctor --json')
            ->expectsOutputToContain('"label": "Service binding"')
            ->assertExitCode(0);
    }

    public function test_strict_fails_when_warnings_exist(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', []);

        $this->artisan('zarbin-seo:doctor --strict')
            ->expectsOutputToContain('warning')
            ->assertExitCode(1);
    }

    public function test_warnings_are_shown_in_output(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', []);

        $this->artisan('zarbin-seo:doctor')
            ->expectsOutputToContain('Localization locales')
            ->expectsOutputToContain('warning')
            ->assertExitCode(0);
    }
}
