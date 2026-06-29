<?php

declare(strict_types=1);

namespace Zarbin\Seo\Renderers;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\Html;

final class SeoRenderer
{
    public function __construct(
        private readonly TitleRenderer $titles = new TitleRenderer,
        private readonly MetaRenderer $meta = new MetaRenderer,
        private readonly AlternateLanguageRenderer $alternateLanguages = new AlternateLanguageRenderer,
        private readonly OpenGraphRenderer $openGraph = new OpenGraphRenderer,
        private readonly TwitterCardRenderer $twitter = new TwitterCardRenderer,
        private readonly JsonLdRenderer $jsonLd = new JsonLdRenderer,
    ) {}

    public function title(SeoData $data): string
    {
        return $this->titles->render($data);
    }

    public function meta(SeoData $data): string
    {
        return $this->meta->basic($data);
    }

    public function openGraph(SeoData $data): string
    {
        return $this->openGraph->render($data);
    }

    public function alternateLanguages(SeoData $data): string
    {
        return $this->alternateLanguages->render($data);
    }

    public function twitter(SeoData $data): string
    {
        return $this->twitter->render($data);
    }

    public function jsonLd(SeoData $data): string
    {
        return $this->jsonLd->render($data);
    }

    public function render(SeoData $data, bool $minify = false): string
    {
        return Html::lines([
            $this->title($data),
            $this->meta($data),
            $this->alternateLanguages($data),
            $this->openGraph($data),
            $this->twitter($data),
            $this->jsonLd($data),
        ], $minify);
    }
}
