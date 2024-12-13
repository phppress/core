<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di;

use PHPPress\Di\{Container, ReflectionFactory};
use PHPUnit\Framework\Attributes\Group;

/**
 * Test case for the ReflectionFactory class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see \PHPPress\LICENSE}
 */
#[Group('di')]
final class ReflectionFactoryTest extends \PHPUnit\Framework\TestCase
{
    private ReflectionFactory $factory;

    public function setUp(): void
    {
        $this->factory = new ReflectionFactory(new Container());

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->factory);

        parent::tearDown();
    }

    public function testInvokeDefinitionUsingAdditionalParameters(): void
    {
        $invoke = $this->factory->invoke(
            static fn(string $param1, ...$additionalParams): array => [$param1, $additionalParams],
            ['First', ['Second', 'Third']],
        );

        $this->assertSame(['First', ['Second', 'Third']], $invoke);
    }
}
