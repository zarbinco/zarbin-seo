<?php

declare(strict_types=1);

namespace Zarbin\Seo\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class SitemapCommand extends Command
{
    protected $signature = 'zarbin-seo:sitemap
        {--locale= : Generate sitemap for a specific locale}
        {--index : Generate sitemap index instead of URL sitemap}
        {--output= : Write XML output to a file path}
        {--count : Only print URL count}
        {--dry-run : Show summary without writing files}';

    protected $description = 'Generate or preview Zarbin SEO sitemap XML.';

    public function handle(): int
    {
        $locale = $this->optionString('locale');
        $output = $this->optionString('output');

        if ($this->option('count')) {
            $this->line('Sitemap URLs: '.count(seo()->sitemapUrls($locale)));

            return self::SUCCESS;
        }

        $xml = $this->option('index')
            ? seo()->sitemapIndex()
            : seo()->sitemap($locale);

        if ($this->option('dry-run')) {
            $kind = $this->option('index') ? 'sitemap index' : 'URL sitemap';
            $this->line("Dry run: would generate {$kind}.");

            if ($output !== null) {
                $this->line("Dry run: would write to {$output}.");
            }

            return self::SUCCESS;
        }

        if ($output !== null) {
            return $this->writeOutput($output, $xml);
        }

        $this->output->write($xml.PHP_EOL, false, OutputInterface::OUTPUT_RAW);

        return self::SUCCESS;
    }

    private function writeOutput(string $path, string $content): int
    {
        try {
            $directory = dirname($path);

            if ($directory !== '.' && ! is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            if (file_put_contents($path, $content) === false) {
                $this->error("Could not write sitemap to {$path}.");

                return self::FAILURE;
            }
        } catch (Throwable $exception) {
            $this->error("Could not write sitemap to {$path}: ".$exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Sitemap written to {$path}.");

        return self::SUCCESS;
    }

    private function optionString(string $key): ?string
    {
        $value = $this->option($key);

        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
