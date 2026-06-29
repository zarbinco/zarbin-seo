<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Zarbin\Seo\Tests\TestCase;

final class RobotsCommandTest extends TestCase
{
    public function test_robots_command_outputs_user_agent(): void
    {
        $this->artisan('zarbin-seo:robots')
            ->expectsOutputToContain('User-agent: *')
            ->assertExitCode(0);
    }

    public function test_output_writes_file(): void
    {
        $path = $this->tempPath('robots.txt');

        try {
            $this->artisan('zarbin-seo:robots', ['--output' => $path])
                ->expectsOutputToContain('Robots.txt written')
                ->assertExitCode(0);

            $this->assertFileExists($path);
            $this->assertStringContainsString('User-agent: *', (string) file_get_contents($path));
        } finally {
            $this->removeTempPath($path);
        }
    }

    public function test_dry_run_does_not_write_file(): void
    {
        $path = $this->tempPath('dry-run-robots.txt');

        try {
            $this->artisan('zarbin-seo:robots', ['--output' => $path, '--dry-run' => true])
                ->expectsOutputToContain('Dry run')
                ->assertExitCode(0);

            $this->assertFileDoesNotExist($path);
        } finally {
            $this->removeTempPath($path);
        }
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
