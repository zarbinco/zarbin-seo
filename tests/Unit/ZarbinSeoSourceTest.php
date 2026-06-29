<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Illuminate\Support\Facades\Route;
use Zarbin\Seo\Tests\TestCase;

final class ZarbinSeoSourceTest extends TestCase
{
    public function test_for_model_sets_current_data(): void
    {
        $model = new ManagerSourcePlainModel;
        $model->name = 'Model name';

        $data = seo()->reset()->for($model)->get();

        $this->assertSame('Model name', $data->title);
    }

    public function test_route_sets_current_data(): void
    {
        config()->set('zarbin-seo.routes.home', ['title' => 'Home']);

        $data = seo()->reset()->route('home')->get();

        $this->assertSame('Home', $data->title);
    }

    public function test_resolve_does_not_mutate_current_manager_data(): void
    {
        $model = new ManagerSourcePlainModel;
        $model->name = 'Resolved model';

        $manager = seo()->reset()->title('Current title');
        $resolved = $manager->resolve($model);

        $this->assertSame('Resolved model', $resolved->title);
        $this->assertSame('Current title', $manager->get()->title);
    }

    public function test_fluent_setters_still_work_after_for(): void
    {
        $model = new ManagerSourcePlainModel;
        $model->name = 'Model name';

        $data = seo()
            ->reset()
            ->for($model)
            ->title('Manual title')
            ->description('Manual description')
            ->get();

        $this->assertSame('Manual title', $data->title);
        $this->assertSame('Manual description', $data->description);
    }

    public function test_reset_restores_defaults(): void
    {
        config()->set('zarbin-seo.defaults.title', 'Default title');

        $manager = seo()->reset()->title('Changed title');

        $this->assertSame('Changed title', $manager->get()->title);
        $this->assertSame('Default title', $manager->reset()->get()->title);
    }

    public function test_route_method_can_generate_canonical(): void
    {
        Route::get('/home', fn (): string => 'home')->name('home');

        $data = seo()->reset()->route('home')->get();

        $this->assertStringEndsWith('/home', $data->canonical);
    }
}

final class ManagerSourcePlainModel
{
    public ?string $name = null;
}
