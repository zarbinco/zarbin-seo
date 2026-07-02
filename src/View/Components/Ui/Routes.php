<?php

declare(strict_types=1);

namespace Zarbin\Seo\View\Components\Ui;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Zarbin\Seo\Support\UiComponentDataFactory;

final class Routes extends Component
{
    public function __construct(
        public ?string $locale = null,
        public ?string $editUrlBase = null,
        public bool $showActions = true,
    ) {}

    public function render(): View
    {
        return view('zarbin-seo::components.ui.routes', (new UiComponentDataFactory)->routes(
            $this->locale,
            $this->editUrlBase,
            $this->showActions,
        ));
    }
}
