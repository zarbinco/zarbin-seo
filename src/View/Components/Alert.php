<?php

declare(strict_types=1);

namespace Zarbin\Seo\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Zarbin\Seo\Support\UiComponentDataFactory;

final class Alert extends Component
{
    public function __construct(
        public string $type = 'warning',
        public ?string $message = null,
        public ?string $locale = null,
        public bool $session = false,
    ) {}

    public function render(): View
    {
        return view('zarbin-seo::components.alert', array_replace(
            (new UiComponentDataFactory)->directionData($this->locale),
            [
                'type' => $this->type,
                'message' => $this->message,
                'session' => $this->session,
            ],
        ));
    }
}
