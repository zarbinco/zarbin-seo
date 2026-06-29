<?php

declare(strict_types=1);

namespace Zarbin\Seo\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

final class RobotsCommand extends Command
{
    protected $signature = 'zarbin-seo:robots
        {--output= : Write robots.txt output to a file path}
        {--dry-run : Show summary without writing files}';

    protected $description = 'Generate or preview Zarbin SEO robots.txt output.';

    public function handle(): int
    {
        $output = $this->optionString('output');
        $content = seo()->robotsTxt();

        if ($this->option('dry-run')) {
            $this->line('Dry run: would generate robots.txt.');

            if ($output !== null) {
                $this->line("Dry run: would write to {$output}.");
            }

            return self::SUCCESS;
        }

        if ($output !== null) {
            return $this->writeOutput($output, $content);
        }

        $this->output->write($content.PHP_EOL, false, OutputInterface::OUTPUT_RAW);

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
                $this->error("Could not write robots.txt to {$path}.");

                return self::FAILURE;
            }
        } catch (Throwable $exception) {
            $this->error("Could not write robots.txt to {$path}: ".$exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Robots.txt written to {$path}.");

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
