<?php

declare(strict_types=1);

namespace Zarbin\Seo\View\Components\Ui;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Zarbin\Seo\Support\UiComponentDataFactory;

final class RouteForm extends Component
{
    public function __construct(
        public string $route,
        public ?string $locale = null,
        public ?string $action = null,
        public ?string $deleteAction = null,
        public bool $standalone = false,
        public bool $showPreview = true,
        public bool $showRawHtml = true,
    ) {}

    public function render(): View
    {
        return view('zarbin-seo::components.ui.route-form', array_replace(
            (new UiComponentDataFactory)->routeForm(
                $this->route,
                $this->locale,
                $this->action,
                $this->deleteAction,
                $this->showPreview,
                $this->showRawHtml,
            ),
            ['standalone' => $this->standalone],
        ));
    }
}
