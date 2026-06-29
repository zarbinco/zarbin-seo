<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature\Bulletproof;

use Generator;
use Zarbin\Seo\Tests\TestCase;

final class CommandSafetyTest extends TestCase
{
    public function test_doctor_reports_broken_config_without_fatal_errors(): void
    {
        config()->set('app.url', '');
        config()->set('zarbin-seo.localization.enabled', true);
        config()->set('zarbin-seo.localization.locales', []);
        config()->set('zarbin-seo.localization.missing_translation_strategy', 'broken');

        $this->artisan('zarbin-seo:doctor')
            ->assertExitCode(0)
            ->expectsOutputToContain('Localization is enabled but no locales are configured');
    }

    public function test_check_command_handles_missing_route_and_invalid_models(): void
    {
        $this->artisan('zarbin-seo:check', ['--route' => 'missing'])
            ->assertExitCode(1)
            ->expectsOutputToContain('Route [missing] is not configured');

        $this->artisan('zarbin-seo:check', ['--model' => 'Nope\\Missing', '--id' => '1'])
            ->assertExitCode(1)
            ->expectsOutputToContain('does not exist');

        $this->artisan('zarbin-seo:check', ['--model' => CommandSafetyModel::class])
            ->assertExitCode(1)
            ->expectsOutputToContain('--id is required');
    }

    public function test_sitemap_command_survives_invalid_sitemap_source(): void
    {
        config()->set('zarbin-seo.models.'.CommandSafetyModel::class, [
            'sitemap' => true,
            'sitemap_source' => static function (): Generator {
                throw new \RuntimeException('bad source');
                yield new CommandSafetyModel;
            },
        ]);

        $this->artisan('zarbin-seo:sitemap', ['--count' => true])
            ->assertExitCode(0)
            ->expectsOutputToContain('Sitemap URLs: 0');
    }

    public function test_robots_command_handles_malformed_config(): void
    {
        config()->set('zarbin-seo.robots_txt.allow', 'not an array');
        config()->set('zarbin-seo.robots_txt.disallow', [null, '/private']);
        config()->set('zarbin-seo.robots_txt.sitemaps', [null]);

        $this->artisan('zarbin-seo:robots')
            ->assertExitCode(0)
            ->expectsOutputToContain('User-agent: *');
    }

    public function test_install_command_is_safe_by_default(): void
    {
        $this->artisan('zarbin-seo:install')
            ->assertExitCode(0)
            ->expectsOutputToContain('Zarbin SEO install completed')
            ->expectsOutputToContain('The optional Blade UI is disabled by default');
    }
}

final class CommandSafetyModel {}
