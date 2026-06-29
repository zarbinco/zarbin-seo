<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Zarbin\Seo\Tests\TestCase;

final class CheckCommandTest extends TestCase
{
    public function test_command_without_args_shows_configured_summary(): void
    {
        config()->set('zarbin-seo.routes', ['home' => ['title' => 'Home']]);
        config()->set('zarbin-seo.models', [CheckCommandDummyModel::class => ['title' => 'title']]);

        $this->artisan('zarbin-seo:check')
            ->expectsOutputToContain('Routes: 1')
            ->expectsOutputToContain('Models: 1')
            ->assertExitCode(0);
    }

    public function test_route_fields_output_shows_resolved_fields(): void
    {
        $this->configureHomeRoute();

        $this->artisan('zarbin-seo:check', ['--route' => 'home'])
            ->expectsOutputToContain('Home')
            ->expectsOutputToContain('https://example.com')
            ->expectsOutputToContain('index, follow')
            ->assertExitCode(0);
    }

    public function test_route_json_outputs_valid_json(): void
    {
        $this->configureHomeRoute();

        $this->artisan('zarbin-seo:check', ['--route' => 'home', '--json' => true])
            ->expectsOutputToContain('"title": "Home"')
            ->assertExitCode(0);
    }

    public function test_route_render_outputs_html(): void
    {
        $this->configureHomeRoute();

        $this->artisan('zarbin-seo:check', ['--route' => 'home', '--render' => true])
            ->expectsOutputToContain('<title>')
            ->assertExitCode(0);
    }

    public function test_missing_route_returns_failure(): void
    {
        $this->artisan('zarbin-seo:check', ['--route' => 'missing'])
            ->expectsOutputToContain('is not configured')
            ->assertExitCode(1);
    }

    public function test_model_with_id_resolves_data(): void
    {
        Schema::create('check_command_dummy_models', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
        });

        $model = CheckCommandDummyModel::query()->create([
            'title' => 'Stored title',
            'description' => 'Stored description',
        ]);

        config()->set('zarbin-seo.models.'.CheckCommandDummyModel::class, [
            'title' => 'title',
            'description' => 'description',
        ]);

        $this->artisan('zarbin-seo:check', [
            '--model' => CheckCommandDummyModel::class,
            '--id' => (string) $model->getKey(),
            '--fields' => true,
        ])
            ->expectsOutputToContain('Stored title')
            ->expectsOutputToContain('Stored description')
            ->assertExitCode(0);
    }

    private function configureHomeRoute(): void
    {
        config()->set('zarbin-seo.routes', [
            'home' => [
                'title' => 'Home',
                'description' => 'Welcome home',
                'canonical' => 'https://example.com',
                'robots' => ['index', 'follow'],
                'schema' => 'WebPage',
            ],
        ]);
    }
}

final class CheckCommandDummyModel extends Model
{
    protected $guarded = [];

    public $timestamps = false;
}
