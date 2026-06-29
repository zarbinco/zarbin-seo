<?php

declare(strict_types=1);

namespace Zarbin\Seo\Console\Commands;

use Illuminate\Console\Command;

final class InstallCommand extends Command
{
    protected $signature = 'zarbin-seo:install
        {--force : Overwrite published files}
        {--config : Publish config only}
        {--migrations : Publish migrations only}
        {--views : Publish views only}
        {--all : Publish config, migrations, and views}
        {--run-migrations : Run database migrations after publishing migrations}';

    protected $description = 'Install Zarbin SEO package resources.';

    public function handle(): int
    {
        $tags = $this->tagsToPublish();

        foreach ($tags as $tag) {
            $this->publish($tag);
        }

        $this->newLine();
        $this->info('Zarbin SEO install completed.');
        $this->line('Database overrides require enabling features.database_overrides and database.enabled.');
        $this->line('The optional Blade UI is disabled by default.');

        if ($this->option('run-migrations')) {
            if (! $this->shouldPublishMigrations()) {
                $this->warn('--run-migrations was ignored because migrations were not requested.');
            } elseif ($this->shouldRunMigrations()) {
                $this->call('migrate');
            } else {
                $this->warn('Migration run cancelled.');
            }
        }

        $this->newLine();
        $this->line('Next steps:');
        $this->line(' - Review config/zarbin-seo.php');
        $this->line(' - Publish and run migrations only if you want database overrides.');
        $this->line(' - Enable UI only if you want the optional plain Blade screens.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function tagsToPublish(): array
    {
        if ($this->option('all')) {
            return ['zarbin-seo-config', 'zarbin-seo-migrations', 'zarbin-seo-views'];
        }

        $tags = [];

        if ($this->option('config')) {
            $tags[] = 'zarbin-seo-config';
        }

        if ($this->option('migrations')) {
            $tags[] = 'zarbin-seo-migrations';
        }

        if ($this->option('views')) {
            $tags[] = 'zarbin-seo-views';
        }

        return $tags === [] ? ['zarbin-seo-config'] : $tags;
    }

    private function publish(string $tag): void
    {
        $this->line("Publishing {$tag}...");

        $arguments = [
            '--tag' => $tag,
        ];

        if ($this->option('force')) {
            $arguments['--force'] = true;
        }

        $this->call('vendor:publish', $arguments);
    }

    private function shouldPublishMigrations(): bool
    {
        return (bool) $this->option('all') || (bool) $this->option('migrations');
    }

    private function shouldRunMigrations(): bool
    {
        if (! $this->input->isInteractive()) {
            return true;
        }

        return $this->confirm('Run database migrations now?', false);
    }
}
