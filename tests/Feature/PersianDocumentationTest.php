<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Feature;

use Zarbin\Seo\Tests\TestCase;

final class PersianDocumentationTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $expectedDocs = [
        'README.md',
        'installation.md',
        'quick-start.md',
        'model-aware-seo.md',
        'holder-pages.md',
        'route-seo.md',
        'rendering.md',
        'multilingual.md',
        'sitemap-robots.md',
        'database-overrides.md',
        'ui.md',
        'commerce-schema.md',
        'commands.md',
        'testing-hardening.md',
        'config-reference.md',
    ];

    public function test_persian_documentation_files_exist(): void
    {
        foreach ($this->expectedDocs as $file) {
            $this->assertFileExists($this->path("docs/fa/{$file}"));
        }
    }

    public function test_root_readme_prominently_links_to_persian_documentation(): void
    {
        $readme = $this->contents('README.md');
        $top = mb_substr($readme, 0, 700);

        $this->assertStringContainsString('docs/fa/README.md', $top);
        $this->assertStringContainsString('English', $top);
        $this->assertStringContainsString('فارسی', $top);
        $this->assertStringContainsString('## Documentation', $readme);
    }

    public function test_persian_index_links_back_to_english_and_key_docs(): void
    {
        $readme = $this->contents('docs/fa/README.md');

        $this->assertStringContainsString('../../README.md', $readme);
        $this->assertStringContainsString('این پکیج دارای مستندات فارسی و انگلیسی است', $readme);

        foreach ([
            'installation.md',
            'quick-start.md',
            'model-aware-seo.md',
            'sitemap-robots.md',
            'database-overrides.md',
            'commerce-schema.md',
            'testing-hardening.md',
            'config-reference.md',
        ] as $file) {
            $this->assertStringContainsString($file, $readme);
        }
    }

    public function test_persian_docs_contain_persian_text_and_important_commands(): void
    {
        $combined = '';

        foreach ($this->expectedDocs as $file) {
            $combined .= "\n".$this->contents("docs/fa/{$file}");
        }

        $this->assertMatchesRegularExpression('/[\x{0600}-\x{06FF}]/u', $combined);
        $this->assertStringContainsString('composer require zarbinco/zarbin-seo', $combined);
        $this->assertStringContainsString('php artisan zarbin-seo:doctor', $combined);
        $this->assertStringContainsString('php artisan zarbin-seo:sitemap', $combined);
    }

    public function test_changelog_mentions_persian_documentation_for_release_version(): void
    {
        $changelog = $this->contents('CHANGELOG.md');
        $section = str($changelog)->between('## 0.1.1 - 2026-06-29', '## 0.1.0')->toString();

        $this->assertStringContainsString('Added Persian documentation', $section);
    }

    public function test_persian_docs_describe_locale_url_strategies_and_localized_sitemaps(): void
    {
        $combined = $this->contents('docs/fa/multilingual.md')
            .$this->contents('docs/fa/sitemap-robots.md')
            .$this->contents('docs/fa/config-reference.md');

        $this->assertStringContainsString('/about', $combined);
        $this->assertStringContainsString('/fa/about', $combined);
        $this->assertStringContainsString('/en/about', $combined);
        $this->assertStringContainsString('sitemap-fa.xml', $combined);
        $this->assertStringContainsString('sitemap-en.xml', $combined);
        $this->assertStringContainsString('base_url', $combined);
        $this->assertStringContainsString('content_type', $combined);
        $this->assertStringContainsString('text/xml; charset=UTF-8', $combined);
        $this->assertStringContainsString('include_alternates', $combined);
        $this->assertStringContainsString('xhtml:link', $combined);
        $this->assertStringContainsString('sunich.test', $combined);
        $this->assertStringContainsString("'locale' => 'fa'", $combined);
        $this->assertStringContainsString('localized_urls', $combined);
    }

    private function path(string $path): string
    {
        return dirname(__DIR__, 2).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    private function contents(string $path): string
    {
        return (string) file_get_contents($this->path($path));
    }
}
