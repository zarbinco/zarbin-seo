<?php

declare(strict_types=1);

namespace Zarbin\Seo\View\Components\Ui;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Zarbin\Seo\Support\UiComponentDataFactory;

final class Dashboard extends Component
{
    public function __construct(
        public ?string $locale = null,
    ) {}

    public function render(): View
    {
        return view('zarbin-seo::components.ui.dashboard', (new UiComponentDataFactory)->dashboard($this->locale));
    }
}
