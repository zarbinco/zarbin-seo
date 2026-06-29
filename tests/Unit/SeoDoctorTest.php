<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Support\CommandResult;
use Zarbin\Seo\Support\SeoDoctor;
use Zarbin\Seo\Tests\TestCase;

final class SeoDoctorTest extends TestCase
{
    public function test_returns_package_binding_and_config_checks(): void
    {
        $results = (new SeoDoctor)->results();

        $this->assertTrue($this->hasStatus($results, 'Service binding', 'ok'));
        $this->assertTrue($this->hasStatus($results, 'Configuration', 'ok'));
    }

    public function test_warns_when_localization_enabled_without_locales(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', []);

        $doctor = new SeoDoctor;

        $this->assertTrue($this->hasStatus($doctor->results(), 'Localization locales', 'warning'));
        $this->assertTrue($doctor->hasWarnings());
    }

    public function test_warns_when_database_overrides_enabled_but_table_missing(): void
    {
        config()->set('zarbin-seo.features.database_overrides', true);
        config()->set('zarbin-seo.database.enabled', true);

        $doctor = new SeoDoctor;

        $this->assertTrue($this->hasStatus($doctor->results(), 'Database table', 'warning'));
    }

    public function test_warns_when_ui_enabled_but_database_is_not_ready(): void
    {
        config()->set('zarbin-seo.features.ui', true);
        config()->set('zarbin-seo.ui.enabled', true);

        $doctor = new SeoDoctor;

        $this->assertTrue($this->hasStatus($doctor->results(), 'UI database', 'warning'));
    }

    public function test_reports_configured_route_and_model_counts(): void
    {
        config()->set('zarbin-seo.routes', ['home' => ['title' => 'Home']]);
        config()->set('zarbin-seo.models', [self::class => ['title' => 'name']]);

        $results = (new SeoDoctor)->results();

        $routeResult = $this->resultFor($results, 'Configured routes');
        $modelResult = $this->resultFor($results, 'Configured models');

        $this->assertSame(1, $routeResult?->context['count']);
        $this->assertSame(1, $modelResult?->context['count']);
    }

    public function test_reports_commerce_status(): void
    {
        config()->set('zarbin-seo.features.commerce', true);
        config()->set('zarbin-seo.commerce.enabled', true);

        $this->assertTrue($this->hasStatus((new SeoDoctor)->results(), 'Commerce', 'ok'));
    }

    public function test_has_errors_and_warnings_work(): void
    {
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', []);

        $doctor = new SeoDoctor;

        $this->assertFalse($doctor->hasErrors());
        $this->assertTrue($doctor->hasWarnings());
    }

    /**
     * @param  array<int, CommandResult>  $results
     */
    private function hasStatus(array $results, string $label, string $status): bool
    {
        $result = $this->resultFor($results, $label);

        return $result !== null && $result->status === $status;
    }

    /**
     * @param  array<int, CommandResult>  $results
     */
    private function resultFor(array $results, string $label): mixed
    {
        foreach ($results as $result) {
            if ($result->label === $label) {
                return $result;
            }
        }

        return null;
    }
}
