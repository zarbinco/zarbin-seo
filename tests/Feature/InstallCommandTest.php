<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Zarbin\Seo\Tests\TestCase;

final class InstallCommandTest extends TestCase
{
    public function test_install_command_runs_successfully(): void
    {
        $this->artisan('zarbin-seo:install')
            ->expectsOutputToContain('Publishing zarbin-seo-config')
            ->expectsOutputToContain('Zarbin SEO install completed.')
            ->assertExitCode(0);
    }

    public function test_all_option_runs_successfully(): void
    {
        $this->artisan('zarbin-seo:install', ['--all' => true])
            ->expectsOutputToContain('Publishing zarbin-seo-config')
            ->expectsOutputToContain('Publishing zarbin-seo-migrations')
            ->expectsOutputToContain('Publishing zarbin-seo-views')
            ->assertExitCode(0);
    }

    public function test_run_migrations_is_safe_in_no_interaction_mode(): void
    {
        $this->artisan('zarbin-seo:install', [
            '--migrations' => true,
            '--run-migrations' => true,
            '--no-interaction' => true,
        ])->assertExitCode(0);
    }
}
