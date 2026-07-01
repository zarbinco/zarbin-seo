<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Zarbin\Seo\Tests\TestCase;

final class SitemapCommandTest extends TestCase
{
    public function test_sitemap_command_outputs_xml(): void
    {
        $this->configureSitemapRoute();

        $this->artisan('zarbin-seo:sitemap')
            ->expectsOutputToContain('<?xml version="1.0" encoding="UTF-8"?>')
            ->assertExitCode(0);
    }

    public function test_sitemap_command_output_is_unchanged_by_http_content_type_config(): void
    {
        $this->configureSitemapRoute();
        config()->set('zarbin-seo.sitemap.content_type', 'text/xml; charset=UTF-8');

        $this->artisan('zarbin-seo:sitemap')
            ->expectsOutputToContain('<?xml version="1.0" encoding="UTF-8"?>')
            ->doesntExpectOutputToContain('text/xml; charset=UTF-8')
            ->assertExitCode(0);
    }

    public function test_count_outputs_url_count(): void
    {
        $this->configureSitemapRoute();

        $this->artisan('zarbin-seo:sitemap --count')
            ->expectsOutputToContain('Sitemap URLs: 1')
            ->assertExitCode(0);
    }

    public function test_index_outputs_sitemap_index_xml(): void
    {
        config()->set('app.url', 'https://example.com');

        $this->artisan('zarbin-seo:sitemap --index')
            ->expectsOutputToContain('<sitemapindex')
            ->assertExitCode(0);
    }

    public function test_output_writes_file(): void
    {
        $this->configureSitemapRoute();
        $path = $this->tempPath('sitemap.xml');

        try {
            $this->artisan('zarbin-seo:sitemap', ['--output' => $path])
                ->expectsOutputToContain('Sitemap written')
                ->assertExitCode(0);

            $this->assertFileExists($path);
            $this->assertStringContainsString('<loc>https://example.com/home</loc>', (string) file_get_contents($path));
        } finally {
            $this->removeTempPath($path);
        }
    }

    public function test_dry_run_does_not_write_file(): void
    {
        $path = $this->tempPath('dry-run-sitemap.xml');

        try {
            $this->artisan('zarbin-seo:sitemap', ['--output' => $path, '--dry-run' => true])
                ->expectsOutputToContain('Dry run')
                ->assertExitCode(0);

            $this->assertFileDoesNotExist($path);
        } finally {
            $this->removeTempPath($path);
        }
    }

    private function configureSitemapRoute(): void
    {
        config()->set('zarbin-seo.routes', [
            'home' => [
                'canonical' => 'https://example.com/home',
                'sitemap' => true,
            ],
        ]);
    }

    private function tempPath(string $file): string
    {
        return sys_get_temp_dir().DIRECTORY_SEPARATOR.'zarbin-seo-tests-'.uniqid().DIRECTORY_SEPARATOR.$file;
    }

    private function removeTempPath(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
        }

        $directory = dirname($path);

        if (is_dir($directory)) {
            rmdir($directory);
        }
    }
}
