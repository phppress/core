<?php

declare(strict_types=1);

namespace PHPPress\Tests\Exception;

use PHPPress\Exception\UnknownProperty;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;

/**
 * Test case for the {@see UnknownProperty} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('exception')]
final class UnknownPropertyTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor(): void
    {
        $exception = new UnknownProperty();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithFriendlyMessage(): void
    {
        $exception = new UnknownProperty('Test message');

        $this->assertSame('Unknown property: "Test message"', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithArguments(): void
    {
        $previousException = new RuntimeException('Previous exception');
        $exception = new UnknownProperty('Test message', 123, $previousException);

        $this->assertSame('Unknown property: "Test message"', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
