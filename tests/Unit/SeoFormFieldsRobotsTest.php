<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use Zarbin\Seo\Support\SeoFormFields;
use Zarbin\Seo\Tests\TestCase;

final class SeoFormFieldsRobotsTest extends TestCase
{
    public function test_robots_options_return_defaults(): void
    {
        $options = SeoFormFields::robotsOptions();

        $this->assertSame('Index, Follow', $options['index, follow']);
        $this->assertSame('Noindex, Nofollow', $options['noindex, nofollow']);
    }

    public function test_robots_options_accept_custom_config(): void
    {
        config()->set('zarbin-seo.ui.robots_options', [
            'noindex, noarchive' => 'Noindex, Noarchive',
        ]);

        $options = SeoFormFields::robotsOptions();

        $this->assertSame('Noindex, Noarchive', $options['noindex, noarchive']);
        $this->assertSame('Index, Follow', $options['index, follow']);
    }

    public function test_malformed_robots_options_fall_back_safely(): void
    {
        config()->set('zarbin-seo.ui.robots_options', 'broken');

        $options = SeoFormFields::robotsOptions();

        $this->assertSame('Index, Follow', $options['index, follow']);
    }

    public function test_flatten_override_data_still_normalizes_robots_string(): void
    {
        $data = SeoFormFields::flattenOverrideData([
            'seo' => [
                'robots' => 'noindex, follow',
            ],
        ]);

        $this->assertSame(['noindex', 'follow'], $data['robots']);
    }
}
