<?php

declare(strict_types=1);

namespace PHPPress\Tests\Exception;

use PHPPress\Exception\InvalidConfig;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;

/**
 * Test case for the InvalidConfig class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('exception')]
final class InvalidConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor(): void
    {
        $exception = new InvalidConfig();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithFriendlyMessage(): void
    {
        $exception = new InvalidConfig('Test message');

        $this->assertSame('Invalid configuration: "Test message"', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithArguments(): void
    {
        $previousException = new RuntimeException('Previous exception');
        $exception = new InvalidConfig('Test message', 123, $previousException);

        $this->assertSame('Invalid configuration: "Test message"', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}