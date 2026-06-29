<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zarbin\Seo\Support\Html;

final class HtmlSupportTest extends TestCase
{
    public function test_escapes_values(): void
    {
        $this->assertSame('Tom &amp; Jerry', Html::escape('Tom & Jerry'));
    }

    public function test_renders_attributes(): void
    {
        $this->assertSame(' name="description" content="Tom &amp; Jerry"', Html::attributes([
            'name' => 'description',
            'content' => 'Tom & Jerry',
        ]));
    }

    public function test_skips_null_and_false_attributes(): void
    {
        $this->assertSame(' name="robots"', Html::attributes([
            'name' => 'robots',
            'content' => null,
            'hidden' => false,
        ]));
    }

    public function test_renders_boolean_attributes(): void
    {
        $this->assertSame(' disabled', Html::attributes(['disabled' => true]));
    }

    public function test_renders_normal_tags(): void
    {
        $this->assertSame('<title>Tom &amp; Jerry</title>', Html::tag('title', content: 'Tom & Jerry'));
    }

    public function test_joins_lines_with_and_without_minify(): void
    {
        $lines = ['<title>A</title>', '', '<meta name="robots" content="index">'];

        $this->assertSame('<title>A</title>'.PHP_EOL.'<meta name="robots" content="index">', Html::lines($lines));
        $this->assertSame('<title>A</title><meta name="robots" content="index">', Html::lines($lines, true));
    }
}
