<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Definition;

use PHPPress\Exception\InvalidArgument;
use PHPPress\Di\Definition\Instance;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test case for the Instance class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('factory')]
final class InstanceTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorException(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Invalid argument: "The required component "id" is empty."');

        Instance::of('');
    }
}
