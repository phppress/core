<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di;

use PHPPress\Exception\InvalidConfig;
use PHPPress\Di\Instance;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test case for the Instance class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('di')]
final class InstanceTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorException(): void
    {
        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage('Invalid configuration: The required component "id" is empty.');

        Instance::of('');
    }
}
