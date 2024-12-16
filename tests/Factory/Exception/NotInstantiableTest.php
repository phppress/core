<?php

declare(strict_types=1);

namespace PHPPress\Tests\Factory\Exception;

use PHPPress\Factory\Exception\NotInstantiable;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;

/**
 * Test case for the {@see NotInstantiable} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('di')]
final class NotInstantiableTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor(): void
    {
        $exception = new NotInstantiable();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithFriendlyMessage(): void
    {
        $exception = new NotInstantiable('Test message');

        $this->assertSame('Not instantiable exception: "Test message"', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithArguments(): void
    {
        $previousException = new RuntimeException('Previous exception');
        $exception = new NotInstantiable('Test message', 123, $previousException);

        $this->assertSame('Not instantiable exception: "Test message"', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
