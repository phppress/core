<?php

declare(strict_types=1);

namespace PHPPress\Tests\Exception;

use PHPPress\Middleware\Exception\MiddlewareResolution;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;

/**
 * Test case for the {@see \PHPPress\Middleware\Exception\MiddlewareResolution} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('middleware')]
final class MiddlewareResolutionTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor(): void
    {
        $exception = new MiddlewareResolution();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithFriendlyMessage(): void
    {
        $exception = new MiddlewareResolution('Test message');

        $this->assertSame('Middleware resolution: "Test message"', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithArguments(): void
    {
        $previousException = new RuntimeException('Previous exception');
        $exception = new MiddlewareResolution('Test message', 123, $previousException);

        $this->assertSame('Middleware resolution: "Test message"', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
