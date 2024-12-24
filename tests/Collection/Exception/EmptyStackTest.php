<?php

declare(strict_types=1);

namespace PHPPress\Tests\Collection\Exception;

use PHPPress\Collection\Exception\EmptyStack;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;

/**
 * Test case for the {@see \PHPPress\Exception\EmptyStack} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('exception')]
final class EmptyStackTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor(): void
    {
        $exception = new EmptyStack();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithFriendlyMessage(): void
    {
        $exception = new EmptyStack('Test message');

        $this->assertSame('Empty stack: "Test message"', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithArguments(): void
    {
        $previousException = new RuntimeException('Previous exception');
        $exception = new EmptyStack('Test message', 123, $previousException);

        $this->assertSame('Empty stack: "Test message"', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
