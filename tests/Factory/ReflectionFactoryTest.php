<?php

declare(strict_types=1);

namespace PHPPress\Tests\Factory;

use PHPPress\Di\Container;
use PHPPress\Exception\InvalidDefinition;
use PHPPress\Factory\ReflectionFactory;
use PHPUnit\Framework\Attributes\Group;
use Throwable;

/**
 * Test case for the {@see ReflectionFactory} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
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

    /**
     * @throws InvalidDefinition If dependencies cannot be resolved.
     * @throws Throwable If the callback is not valid.
     */
    public function testInvokeDefinitionUsingAdditionalParameters(): void
    {
        $invoke = $this->factory->invoke(
            static fn(string $param1, ...$additionalParams): array => [$param1, $additionalParams],
            ['First', ['Second', 'Third']],
        );

        $this->assertSame(['First', ['Second', 'Third']], $invoke);
    }
}
