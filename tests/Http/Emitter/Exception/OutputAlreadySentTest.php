<?php

declare(strict_types=1);

namespace PHPPress\Tests\Http\Emitter\Exception;

use PHPPress\Http\Emitter\Exception\OutputAlreadySent;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;

/**
 * Test case for the {@see InvalidArgument} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('http')]
final class OutputAlreadySentTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor(): void
    {
        $exception = new OutputAlreadySent();

        $this->assertSame('', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithFriendlyMessage(): void
    {
        $exception = new OutputAlreadySent('Test message');

        $this->assertSame('Output already sent: "Test message"', $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithArguments(): void
    {
        $previousException = new RuntimeException('Previous exception');
        $exception = new OutputAlreadySent('Test message', 123, $previousException);

        $this->assertSame('Output already sent: "Test message"', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
