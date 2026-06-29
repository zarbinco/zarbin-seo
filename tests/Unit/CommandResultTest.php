<?php

declare(strict_types=1);

namespace Zarbin\Seo\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Zarbin\Seo\Support\CommandResult;

final class CommandResultTest extends TestCase
{
    public function test_factories_create_expected_statuses(): void
    {
        $this->assertSame('ok', CommandResult::ok('Check')->status);
        $this->assertSame('warning', CommandResult::warning('Check')->status);
        $this->assertSame('error', CommandResult::error('Check')->status);
        $this->assertSame('info', CommandResult::info('Check')->status);
    }

    public function test_error_and_warning_helpers_work(): void
    {
        $this->assertTrue(CommandResult::error('Check')->isError());
        $this->assertFalse(CommandResult::ok('Check')->isError());
        $this->assertTrue(CommandResult::warning('Check')->isWarning());
        $this->assertFalse(CommandResult::info('Check')->isWarning());
    }

    public function test_to_array_returns_payload(): void
    {
        $result = CommandResult::ok('Config', 'Loaded', ['key' => 'value']);

        $this->assertSame([
            'status' => 'ok',
            'label' => 'Config',
            'message' => 'Loaded',
            'context' => ['key' => 'value'],
        ], $result->toArray());
    }
}
