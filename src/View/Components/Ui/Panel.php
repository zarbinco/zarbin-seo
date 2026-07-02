<?php

declare(strict_types=1);

namespace Zarbin\Seo\View\Components\Ui;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Zarbin\Seo\Support\UiComponentDataFactory;

final class Panel extends Component
{
    public function __construct(
        public ?string $locale = null,
        public bool $showDashboard = true,
        public bool $showRoutes = true,
        public bool $showModels = true,
    ) {}

    public function render(): View
    {
        return view('zarbin-seo::components.ui.panel', array_replace(
            (new UiComponentDataFactory)->directionData($this->locale),
            [
                'locale' => $this->locale,
                'showDashboard' => $this->showDashboard,
                'showRoutes' => $this->showRoutes,
                'showModels' => $this->showModels,
            ],
        ));
    }
}
