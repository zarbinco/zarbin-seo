<?php

declare(strict_types=1);

namespace Zarbin\Seo\Renderers;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\Html;

final class TitleRenderer
{
    public function render(SeoData $data): string
    {
        $title = $this->displayTitle($data);

        return $title === null ? '' : Html::tag('title', content: $title);
    }

    private function displayTitle(SeoData $data): ?string
    {
        $title = $data->title;
        $siteName = $data->siteName;

        if ($title === null || $title === '') {
            return $siteName === null || $siteName === '' ? null : $siteName;
        }

        if ($siteName === null || $siteName === '' || str_contains(mb_strtolower($title), mb_strtolower($siteName))) {
            return $title;
        }

        return $title.' '.$this->separator($data).' '.$siteName;
    }

    private function separator(SeoData $data): string
    {
        $separator = trim((string) $data->separator);

        return $separator === '' ? '|' : $separator;
    }
}
