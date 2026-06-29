<?php

declare(strict_types=1);

namespace Zarbin\Seo\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Meta extends Component
{
    public function __construct(
        public mixed $source = null,
        public ?string $locale = null,
        public bool $minify = false,
    ) {}

    public function render(): View
    {
        return view('zarbin-seo::components.meta', [
            'html' => $this->html(),
        ]);
    }

    private function html(): string
    {
        $manager = seo();

        if ($this->source === null) {
            return $manager->render($this->minify);
        }

        return $manager->renderer()->render(
            $manager->resolve($this->source, $this->locale),
            $this->minify
        );
    }
}
