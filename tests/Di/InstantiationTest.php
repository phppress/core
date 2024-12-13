<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di;

use PHPPress\Di\Exception\NotInstantiable;
use PHPPress\Di\{Container, Instance};
use PHPPress\Exception\InvalidDefinition;
use PHPUnit\Framework\Attributes\{Group};

/**
 * Test cases for dependency injection container instantiation functionality.
 *
 * Verifies container's ability to:
 * - Auto-wire dependencies.
 * - Handle class aliases.
 * - Process interface bindings.
 * - Manage singletons.
 * - Handle method/property definitions.
 * - Process callable definitions.
 * - Validate class existence and definition format.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('di')]
final class InstantiationTest extends \PHPUnit\Framework\TestCase
{
    public function testAutoWired(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\Instance::class);

        $this->assertInstanceOf(Stub\InstanceInterface::class, $instance);
        $this->assertSame(0, $instance->getA());
        $this->assertSame(0, $instance->getB());
    }

    public function testCallable(): void
    {
        $container = $this->createContainer(
            [
                'instance' => static function (): int {
                    return 42;
                },
            ],
        );

        $this->assertSame(42, $container->get('instance'));
    }

    public function testCallableUsingInterfaceClassArguments(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                'instance' => static function (Stub\EngineInterface $engine): string {
                    return $engine->getName();
                },
            ],
        );

        $this->assertSame('Mark One', $container->get('instance'));
    }

    public function testClassAliases(): void
    {
        $container = $this->createContainer(
            [
                'instance' => Stub\Instance::class,
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\Instance::class, $instance);
        $this->assertSame(0, $instance->getA());
        $this->assertSame(0, $instance->getB());
    }

    public function testClassIndirectly(): void
    {
        $container = $this->createContainer(
            [
                'instance' => Stub\Instance::class,
                Stub\Instance::class => ['setA()' => [42]],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\Instance::class, $instance);
        $this->assertSame(42, $instance->getA());
    }

    public function testDefinitionsUsingMethodsAndProperties(): void
    {
        $container = $this->createContainer(
            [
                Stub\Instance::class => [
                    'setA()' => [42],
                    'setB()' => [142],
                    'c' => 242,
                ],
            ],
        );

        $instance = $container->get(Stub\Instance::class);

        $this->assertInstanceOf(Stub\Instance::class, $instance);
        $this->assertSame(42, $instance->getA());
        $this->assertSame(142, $instance->getB());
        $this->assertSame(242, $instance->c);
    }

    public function testFailsForInvalidDefinitionWithIntegerValue(): void
    {
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage('Invalid definition: "Unsupported definition type for "integer"."');

        $this->createContainer(['instance' => 42]);
    }

    public function testFailsForInvalidDefinitionStringValue(): void
    {
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage('Invalid definition: "Invalid definition for "instance": invalid"');

        $this->createContainer(['instance' => 'invalid']);
    }

    public function testFailsForNonExistentClass(): void
    {
        $container = $this->createContainer();

        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage(
            'Not instantiable exception: "Failed to instantiate component or class: "NonExistentClass"."',
        );

        $container->get(\NonExistentClass::class);
    }

    public function testFailsWithInvalidDefinitionArrayValue(): void
    {
        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "A class definition requires a "__class" or "class" member."',
        );

        $this->createContainer(
            [
                'instance' => ['invalid'],
            ],
        );
    }

    public function testFullWiringResolvesCorrectDependencies(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                Stub\EngineCarTunning::class,
                Stub\EngineCar::class,
            ],
        );

        $instance = $container->get(Stub\EngineCarTunning::class);

        $this->assertInstanceOf(Stub\EngineCarTunning::class, $instance);
        $this->assertInstanceOf(Stub\EngineCar::class, $instance->getEngineCar());
        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance->getEngineCar()->getEngine());
        $this->assertSame('Mark One', $instance->getEngineCar()->getEngineName());
    }

    public function testHookClass(): void
    {
        $container = $this->createContainer(
            [
                'hook' => [
                    '__class' => Stub\InstanceHook::class,
                    'firstName' => 'john',
                    'lastName' => 'doe',
                ],
            ],
        );

        $instance = $container->get('hook');

        $this->assertInstanceOf(Stub\InstanceHook::class, $instance);
        $this->assertSame('John Doe', $instance->fullName);
    }

    public function testImmutableMethod(): void
    {
        $container = $this->createContainer(
            [
                Stub\Instance::class => [
                    'withD()' => [1000],
                ],
            ],
        );

        $instance = $container->get(Stub\Instance::class);

        $this->assertInstanceOf(Stub\Instance::class, $instance);
        $this->assertSame(1000, $instance->d);
    }

    public function testInstanceClassArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => Instance::of(Stub\Instance::class),
            ],
        );

        $this->assertInstanceOf(Stub\Instance::class, $container->get('instance'));
    }

    public function testInterfaceBinding(): void
    {
        $container = $this->createContainer(
            [
                Stub\InstanceInterface::class => Stub\Instance::class,
            ],
        );

        $instance = $container->get(Stub\InstanceInterface::class);

        $this->assertInstanceOf(Stub\Instance::class, $instance);
        $this->assertSame(0, $instance->getA());
        $this->assertSame(0, $instance->getB());
    }

    public function testMultipleGetCallsCreateDifferentInstances(): void
    {
        $container = $this->createContainer();

        $instanceOne = $container->get(Stub\Instance::class);
        $instanceTwo = $container->get(Stub\Instance::class);

        $this->assertNotSame($instanceOne, $instanceTwo);
    }

    public function testReturnsExistingSingletonInstance(): void
    {
        $container = $this->createContainer(singletons: [Stub\Instance::class]);

        $instanceOne = $container->get(Stub\Instance::class);
        $instanceTwo = $container->get(Stub\Instance::class);

        $this->assertSame($instanceOne, $instanceTwo);
    }

    private function createContainer($definitions = [], $singletons = []): Container
    {
        return new Container($definitions, $singletons);
    }
}
