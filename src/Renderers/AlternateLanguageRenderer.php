<?php

declare(strict_types=1);

namespace Zarbin\Seo\Renderers;

use Throwable;
use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\Html;
use Zarbin\Seo\Support\LocaleHelper;

final class AlternateLanguageRenderer
{
    public function render(SeoData $data): string
    {
        if (
            ! $this->config('zarbin-seo.features.alternate_languages', true)
            || ! LocaleHelper::shouldGenerateHreflang()
            || ! $data->hasAlternateLanguages()
        ) {
            return '';
        }

        $lines = [];

        foreach ($data->alternateLanguages as $locale => $url) {
            if ($locale === '' || $url === '') {
                continue;
            }

            $lines[] = Html::selfClosingTag('link', [
                'rel' => 'alternate',
                'hreflang' => $locale,
                'href' => $url,
            ]);
        }

        return Html::lines($lines);
    }

    private function config(string $key, mixed $default = null): mixed
    {
        if (! function_exists('config')) {
            return $default;
        }

        try {
            return config($key, $default);
        } catch (Throwable) {
            return $default;
        }
    }
}
