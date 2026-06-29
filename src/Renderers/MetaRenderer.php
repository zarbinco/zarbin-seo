<?php

declare(strict_types=1);

namespace Zarbin\Seo\Renderers;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\Html;

final class MetaRenderer
{
    public function description(SeoData $data): string
    {
        return $data->hasDescription()
            ? Html::selfClosingTag('meta', ['name' => 'description', 'content' => $data->description])
            : '';
    }

    public function canonical(SeoData $data): string
    {
        return $data->hasCanonical()
            ? Html::selfClosingTag('link', ['rel' => 'canonical', 'href' => $data->canonical])
            : '';
    }

    public function robots(SeoData $data): string
    {
        $robots = $data->robotsContent();

        return $robots !== ''
            ? Html::selfClosingTag('meta', ['name' => 'robots', 'content' => $robots])
            : '';
    }

    public function basic(SeoData $data): string
    {
        return Html::lines([
            $this->description($data),
            $this->canonical($data),
            $this->robots($data),
        ]);
    }
}
