<?php

declare(strict_types=1);

namespace Zarbin\Seo;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Renderers\SeoRenderer;
use Zarbin\Seo\Resolvers\SeoSourceResolver;
use Zarbin\Seo\Support\Text;

final class ZarbinSeo
{
    protected SeoData $data;

    public function __construct()
    {
        $this->reset();
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function name(): string
    {
        return 'zarbin-seo';
    }

    public function defaults(): SeoData
    {
        $defaults = $this->config('zarbin-seo.defaults', []);
        $defaults = is_array($defaults) ? $defaults : [];
        $descriptionLimit = $this->descriptionLimit();

        return SeoData::make([
            'title' => Text::clean($defaults['title'] ?? null),
            'description' => Text::limit($defaults['description'] ?? null, $descriptionLimit),
            'image' => $defaults['image'] ?? null,
            'separator' => $defaults['separator'] ?? null,
            'robots' => $defaults['robots'] ?? null,
            'siteName' => $this->config('app.name'),
            'locale' => $this->localeFromApplication(),
        ]);
    }

    public function reset(): self
    {
        $this->data = $this->defaults();

        return $this;
    }

    public function get(): SeoData
    {
        return $this->data;
    }

    public function set(array|SeoData $data): self
    {
        $this->data = $this->data->merge($this->normalizeManagerData($data));

        return $this;
    }

    public function title(?string $title): self
    {
        $this->data = $this->data->withTitle(Text::clean($title));

        return $this;
    }

    public function description(?string $description): self
    {
        $this->data = $this->data->withDescription(Text::limit($description, $this->descriptionLimit()));

        return $this;
    }

    public function canonical(?string $canonical): self
    {
        $this->data = $this->data->withCanonical($canonical);

        return $this;
    }

    public function robots(string|array|null $robots): self
    {
        $this->data = $this->data->withRobots($robots);

        return $this;
    }

    public function image(?string $image): self
    {
        $this->data = $this->data->withImage($image);

        return $this;
    }

    public function type(?string $type): self
    {
        $this->data = $this->data->withType($type);

        return $this;
    }

    public function locale(?string $locale): self
    {
        $this->data = $this->data->withLocale($locale);

        return $this;
    }

    public function siteName(?string $siteName): self
    {
        $this->data = $this->data->withSiteName($siteName);

        return $this;
    }

    public function separator(?string $separator): self
    {
        $this->data = $this->data->withSeparator($separator);

        return $this;
    }

    public function extra(array $extra): self
    {
        return $this->set(['extra' => $extra]);
    }

    public function for(mixed $source, ?string $locale = null): self
    {
        $this->data = $this->resolve($source, $locale);

        return $this;
    }

    /**
     * @param  array<int|string, mixed>  $parameters
     */
    public function route(string $routeName, array $parameters = [], ?string $locale = null): self
    {
        $this->data = $this->resolver()->route($routeName, $parameters, $locale);

        return $this;
    }

    public function resolve(mixed $source = null, ?string $locale = null): SeoData
    {
        return $this->resolver()->resolve($source, $locale);
    }

    public function resolver(): SeoSourceResolver
    {
        return new SeoSourceResolver;
    }

    public function renderer(): SeoRenderer
    {
        return new SeoRenderer;
    }

    public function render(bool $minify = false): string
    {
        return $this->renderer()->render($this->get(), $minify);
    }

    public function titleTag(): string
    {
        return $this->renderer()->title($this->get());
    }

    public function meta(): string
    {
        return $this->renderer()->meta($this->get());
    }

    public function openGraph(): string
    {
        return $this->renderer()->openGraph($this->get());
    }

    public function twitter(): string
    {
        return $this->renderer()->twitter($this->get());
    }

    public function jsonLd(): string
    {
        return $this->renderer()->jsonLd($this->get());
    }

    private function descriptionLimit(): int
    {
        return (int) $this->config('zarbin-seo.defaults.description_limit', 160);
    }

    private function localeFromApplication(): ?string
    {
        if (! function_exists('app')) {
            return null;
        }

        try {
            $app = app();

            return method_exists($app, 'getLocale') ? $app->getLocale() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function config(?string $key = null, mixed $default = null): mixed
    {
        if (! function_exists('config')) {
            return $default;
        }

        try {
            return config($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }

    private function normalizeManagerData(array|SeoData $data): array|SeoData
    {
        if ($data instanceof SeoData) {
            return $data;
        }

        if (array_key_exists('title', $data)) {
            $data['title'] = Text::clean($data['title'] === null ? null : (string) $data['title']);
        }

        if (array_key_exists('description', $data)) {
            $data['description'] = Text::limit(
                $data['description'] === null ? null : (string) $data['description'],
                $this->descriptionLimit()
            );
        }

        return $data;
    }
}
