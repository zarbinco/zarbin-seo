<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Data\SeoData;
use Zarbin\Seo\Support\SeoCompletionChecker;
use Zarbin\Seo\Tests\TestCase;

final class SeoCompletionCheckerTest extends TestCase
{
    public function test_complete_when_required_fields_exist(): void
    {
        $result = $this->checker()->check($this->completeData());

        $this->assertTrue($result['complete']);
        $this->assertSame([], $result['missing']);
    }

    public function test_incomplete_when_title_missing(): void
    {
        $this->assertContains('title', $this->checker()->missing($this->completeData(['title' => null])));
    }

    public function test_incomplete_when_description_missing(): void
    {
        $this->assertContains('description', $this->checker()->missing($this->completeData(['description' => null])));
    }

    public function test_incomplete_when_canonical_missing(): void
    {
        $this->assertContains('canonical', $this->checker()->missing($this->completeData(['canonical' => null])));
    }

    public function test_incomplete_when_robots_missing(): void
    {
        $this->assertContains('robots', $this->checker()->missing($this->completeData(['robots' => []])));
    }

    public function test_recommended_image_is_warning_not_incomplete(): void
    {
        $result = $this->checker()->check($this->completeData(['image' => null]));

        $this->assertTrue($result['complete']);
        $this->assertSame([], $result['missing']);
        $this->assertSame(['image'], $result['warnings']);
    }

    public function test_custom_required_fields_from_config_work(): void
    {
        config()->set('zarbin-seo.ui.completion.required', ['title', 'type']);

        $result = $this->checker()->check(SeoData::make([
            'title' => 'Title',
            'type' => 'Product',
        ]));

        $this->assertTrue($result['complete']);
    }

    public function test_malformed_required_config_does_not_throw(): void
    {
        config()->set('zarbin-seo.ui.completion.required', 'broken');

        $result = $this->checker()->check($this->completeData());

        $this->assertTrue($result['complete']);
    }

    private function checker(): SeoCompletionChecker
    {
        return new SeoCompletionChecker;
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function completeData(array $overrides = []): SeoData
    {
        return SeoData::make(array_replace([
            'title' => 'Title',
            'description' => 'Description',
            'canonical' => 'https://example.test/page',
            'robots' => 'index, follow',
            'image' => 'https://example.test/image.jpg',
        ], $overrides));
    }
}
