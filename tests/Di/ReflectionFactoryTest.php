<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di;

use PHPPress\Di\{Container, ReflectionFactory};
use PHPPress\Exception\InvalidConfig;
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

    public function testResolveCallableDependenciesWithCallbackAsArray(): void
    {
        $callback = [new Stub\ClassInvokeable(), '__invoke'];

        $dependencies = $this->factory->resolveCallableDependencies(
            $callback,
            [],
        );

        $this->assertCount(1, $dependencies);
        $this->assertInstanceOf(\Psr\Container\ContainerInterface::class, $dependencies[0]);
    }

    public function testResolveCallableDependenciesThrowsExceptionForMissingRequiredParameter(): void
    {
        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage('Invalid configuration: "Missing required parameter "requiredParam" when calling "{closure:PHPPress\Tests\Di\ReflectionFactoryTest::testResolveCallableDependenciesThrowsExceptionForMissingRequiredParameter():54}"."');

        $this->factory->resolveCallableDependencies(static fn(string $requiredParam) => $requiredParam);
    }

    public function testResolveCallableDependenciesHandlesAdditionalParams(): void
    {
        $result = $this->factory->resolveCallableDependencies(
            static fn(string $param1, ...$additionalParams): array => [$param1, $additionalParams],
            ['First', 'Second', 'Third'],
        );

        $this->assertCount(3, $result);
        $this->assertSame('First', $result[0]);
        $this->assertSame('Second', $result[1]);
        $this->assertSame('Third', $result[2]);
    }
}
