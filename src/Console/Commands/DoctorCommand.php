<?php

declare(strict_types=1);

namespace Zarbin\Seo\Console\Commands;

use Illuminate\Console\Command;
use Zarbin\Seo\Support\SeoDoctor;

final class DoctorCommand extends Command
{
    protected $signature = 'zarbin-seo:doctor
        {--json : Output results as JSON}
        {--strict : Return failure when warnings exist}';

    protected $description = 'Inspect Zarbin SEO package configuration and readiness.';

    public function handle(SeoDoctor $doctor): int
    {
        $results = $doctor->results();

        if ($this->option('json')) {
            $this->line((string) json_encode(
                array_map(fn ($result): array => $result->toArray(), $results),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ));
        } else {
            $this->table(
                ['status', 'check', 'message'],
                array_map(fn ($result): array => [
                    $result->status,
                    $result->label,
                    $result->message,
                ], $results)
            );
        }

        if ($doctor->hasErrors() || ((bool) $this->option('strict') && $doctor->hasWarnings())) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
