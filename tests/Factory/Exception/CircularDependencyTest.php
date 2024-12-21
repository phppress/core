<?php

declare(strict_types=1);

namespace PHPPress\Tests\Factory\Exception;

use PHPPress\Factory\Exception\CircularDependency;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;

/**
 * Test case for the {@see CircularDependency} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('di')]
final class CircularDependencyTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor(): void
    {
        $exception = new CircularDependency();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithFriendlyMessage(): void
    {
        $exception = new CircularDependency('Test message');

        $this->assertSame('Circular dependency: "Test message"', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithArguments(): void
    {
        $previousException = new RuntimeException('Previous exception');
        $exception = new CircularDependency('Test message', 123, $previousException);

        $this->assertSame('Circular dependency: "Test message"', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
