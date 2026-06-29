<?php

declare(strict_types=1);

namespace Zarbin\Seo\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Renderers\SeoRenderer;

final class CheckCommand extends Command
{
    protected $signature = 'zarbin-seo:check
        {--route= : Resolve a configured route name}
        {--model= : Resolve a model class by key}
        {--id= : Model key to resolve when --model is used}
        {--locale= : Locale to resolve}
        {--json : Output SeoData as JSON}
        {--render : Render full SEO HTML}
        {--fields : Show important resolved fields}';

    protected $description = 'Resolve and inspect SEO data for routes or models.';

    public function handle(): int
    {
        $route = $this->optionString('route');
        $model = $this->optionString('model');
        $locale = $this->optionString('locale');

        if ($route === null && $model === null) {
            return $this->summary();
        }

        if ($route !== null) {
            return $this->checkRoute($route, $locale);
        }

        return $this->checkModel((string) $model, $this->optionString('id'), $locale);
    }

    private function summary(): int
    {
        $routes = $this->configArray('zarbin-seo.routes');
        $models = $this->configArray('zarbin-seo.models');

        $this->info('Zarbin SEO configured sources');
        $this->line('Routes: '.count($routes));
        $this->line('Models: '.count($models));
        $this->line('Use --route=home or --model="App\\Models\\Post" --id=1 to inspect a source.');

        return self::SUCCESS;
    }

    private function checkRoute(string $route, ?string $locale): int
    {
        $routes = $this->configArray('zarbin-seo.routes');

        if (! array_key_exists($route, $routes)) {
            $this->error("Route [{$route}] is not configured in zarbin-seo.routes.");

            return self::FAILURE;
        }

        return $this->outputData(seo()->resolver()->route($route, [], $locale));
    }

    private function checkModel(string $class, ?string $id, ?string $locale): int
    {
        if ($id === null) {
            $this->error('--id is required when --model is used.');

            return self::FAILURE;
        }

        if (! class_exists($class)) {
            $this->error("Model class [{$class}] does not exist.");

            return self::FAILURE;
        }

        $model = $this->findModel($class, $id);

        if ($model === null) {
            $this->error("Model [{$class}] with key [{$id}] could not be found.");

            return self::FAILURE;
        }

        return $this->outputData(seo()->resolve($model, $locale));
    }

    /**
     * @param  class-string  $class
     */
    private function findModel(string $class, string $id): mixed
    {
        try {
            if (method_exists($class, 'query')) {
                $query = $class::query();

                if (is_object($query) && method_exists($query, 'find')) {
                    return $query->find($id);
                }
            }

            if (method_exists($class, 'find')) {
                return $class::find($id);
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private function outputData(SeoData $data): int
    {
        if ($this->option('json')) {
            $this->line((string) json_encode(
                $data->toArray(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ));

            return self::SUCCESS;
        }

        if ($this->option('render')) {
            $this->output->write((new SeoRenderer)->render($data).PHP_EOL, false, OutputInterface::OUTPUT_RAW);

            return self::SUCCESS;
        }

        $this->table(['field', 'value'], [
            ['title', $data->title ?? ''],
            ['description', $data->description ?? ''],
            ['canonical', $data->canonical ?? ''],
            ['robots', $data->robotsContent()],
            ['type', $data->type ?? ''],
            ['locale', $data->locale ?? ''],
            ['image', $data->image ?? ''],
        ]);

        return self::SUCCESS;
    }

    /**
     * @return array<int|string, mixed>
     */
    private function configArray(string $key): array
    {
        $value = config($key, []);

        return is_array($value) ? $value : [];
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
