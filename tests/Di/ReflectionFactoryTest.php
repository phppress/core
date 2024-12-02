<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di;

use Exception;
use PHPPress\Di\Exception\NotInstantiable;
use PHPPress\Di\{Container, Instance, ReflectionFactory};
use PHPPress\Exception\InvalidConfig;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test case for the ReflectionFactory class.
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

    public function testCreateObject(): void
    {
        $objectClassInstance = $this->factory->create(Stub\DefinitionClass::class, ['setA()' => [5]]);
        $this->assertInstanceOf(Stub\DefinitionClass::class, $objectClassInstance);
        $this->assertEquals(5, $objectClassInstance->getA());
    }

    public function testCreateObjectWithNonExistentClassThrowsException(): void
    {
        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage(
            'Not instantiable exception: "Failed to instantiate component or class: "NonExistentClass"."',
        );

        $this->factory->create('NonExistentClass');
    }

    public function testCreateObjectWithDefinitionsConstructor(): void
    {
        $carTunningInstance = $this->factory->create(
            Stub\CarTunning::class,
            definitions: ['__construct()' => ['blue']],
        );

        $this->assertInstanceOf(Stub\CarTunning::class, $carTunningInstance);
        $this->assertSame('blue', $carTunningInstance->color);
    }

    public function testCreateObjectWithConfigPublicProperty(): void
    {
        $carTunningInstance = $this->factory->create(Stub\CarTunning::class, ['color' => 'red']);

        $this->assertInstanceOf(Stub\CarTunning::class, $carTunningInstance);
        $this->assertSame('red', $carTunningInstance->color);
    }

    public function testCreateObjectWithConfigWithSetter(): void
    {
        $engineInstance = $this->factory->create(Stub\EngineMarkOne::class, definitions: ['setNumber()' => [7]]);

        $this->assertInstanceOf(Stub\EngineMarkOne::class, $engineInstance);
        $this->assertSame(7, $engineInstance->getNumber());
    }

    public function testCreateObjectWithConfigWithSetterInmutable(): void
    {
        $engineInstance = $this->factory->create(
            Stub\EngineMarkOneInmutable::class,
            definitions: ['withNumber()' => [5]],
        );

        $this->assertInstanceOf(Stub\EngineMarkOneInmutable::class, $engineInstance);
        $this->assertSame(5, $engineInstance->getNumber());
    }

    public function testCreateObjectWithNotInstantiableClass(): void
    {
        $interfaceClass = Stub\EngineInterface::class;

        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage(
            'Not instantiable exception: "Failed to instantiate component or class: "' . $interfaceClass . '"."',
        );

        $this->factory->create($interfaceClass);
    }

    public function testResolveCallableDependenciesWithInvalidConfigForMissingRequiredParameter(): void
    {
        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage(
            'Invalid configuration: "Missing required parameter "requiredParam" when calling "{closure:PHPPress\Tests\Di\ReflectionFactoryTest::testResolveCallableDependenciesWithInvalidConfigForMissingRequiredParameter():112}"."',
        );

        $this->factory->resolveCallableDependencies(static fn(string $requiredParam) => $requiredParam);
    }

    public function testResolveCallableDependenciesWithParamsIsAsociativeArray(): void
    {
        $betaInstance = new Stub\Beta();

        $result = $this->factory->resolveCallableDependencies(
            static fn(Stub\Beta $param) => $param,
            ['param' => $betaInstance],
        );

        $this->assertCount(1, $result);
        $this->assertSame($betaInstance, $result[0]);
    }

    public function testResolvableCallableDependenciesWithParamsIsAsociativeArrayClass(): void
    {
        $result = $this->factory->resolveCallableDependencies(
            static fn(Stub\Beta $param1, Stub\Beta $param2): array => ['params1' => $param1, 'params2' => $param2],
            [Stub\Beta::class, Stub\Beta::class],
        );

        $this->assertCount(2, $result);
        $this->assertInstanceOf(Stub\Beta::class, $result[0]);
        $this->assertInstanceOf(Stub\Beta::class, $result[1]);
    }

    public function testResolvableCallableDependenciesWithParamsIsAsociativeInstanceClass(): void
    {
        $result = $this->factory->resolveCallableDependencies(
            static fn(Stub\Beta $param1, Stub\Beta $param2): array => ['params1' => $param1, 'params2' => $param2],
            [Instance::of(Stub\Beta::class), Instance::of(Stub\Beta::class)],
        );

        $this->assertCount(2, $result);
        $this->assertInstanceOf(Stub\Beta::class, $result[0]);
        $this->assertInstanceOf(Stub\Beta::class, $result[1]);
    }

    public function testResolveCallableDependenciesWithParamsIsListArray(): void
    {
        $betaInstance = new Stub\Beta();

        $result = $this->factory->resolveCallableDependencies(
            static fn(Stub\Beta $param, null $optionalParam = null) => [$param, $optionalParam],
            ['param' => $betaInstance],
        );

        $this->assertCount(2, $result);
        $this->assertSame($betaInstance, $result[0]);
        $this->assertNull($result[1]);
    }

    public function testResolvableCallableDependenciesWithParamsIsListArrayClass(): void
    {
        $result = $this->factory->resolveCallableDependencies(
            static fn(Stub\Beta $param1, Stub\Beta $param2): array => [$param1, $param2],
            [Stub\Beta::class, Stub\Beta::class],
        );

        $this->assertCount(2, $result);
        $this->assertInstanceOf(Stub\Beta::class, $result[0]);
        $this->assertInstanceOf(Stub\Beta::class, $result[1]);
    }

    public function testResolvableCallableDependenciesWithParamsIsListArrayInstanceClass(): void
    {
        $result = $this->factory->resolveCallableDependencies(
            static fn(Stub\Beta $param1, Stub\Beta $param2): array => [$param1, $param2],
            [Instance::of(Stub\Beta::class), Instance::of(Stub\Beta::class)],
        );

        $this->assertCount(2, $result);
        $this->assertInstanceOf(Stub\Beta::class, $result[0]);
        $this->assertInstanceOf(Stub\Beta::class, $result[1]);
    }

    public function testResolveCallableDependenciesWithStaticFunctionLastArgumentIsOptionalAndVaradic(): void
    {
        $result = $this->factory->resolveCallableDependencies(
            static fn(string $param1, null $optionalParams): array => [$param1, $additionalParams],
            ['First', 'Second', 'Third'],
        );

        $this->assertCount(2, $result);
        $this->assertSame('First', $result[0]);
        $this->assertSame('Second', $result[1]);
    }

    public function testResolveCallableDependenciesWithStaticFunctionNotTypehint(): void
    {
        $result = $this->factory->resolveCallableDependencies(
            static fn($value) => $value !== null,
            ['test'],
        );

        $this->assertCount(1, $result);
        $this->assertSame('test', $result[0]);
    }

    public function testResolveCallableDependenciesWithStaticFunctionPrimitiveTypehint(): void
    {
        $result = $this->factory->resolveCallableDependencies(
            static fn(string|null $value = null) => $value,
            ['value' => 'test'],
        );

        $this->assertCount(1, $result);
        $this->assertSame('test', $result[0]);
    }

    public function testResolveCallableDependenciesWithStaticFunctionLastArgumentIsVaradic(): void
    {
        $result = $this->factory->resolveCallableDependencies(
            static fn(string $param1, string ...$additionalParams): array => [$param1, $additionalParams],
            [
                'param1' => 'First',
                'additionalParams' => ['joe', 'doe'],
            ],
        );

        $this->assertCount(2, $result);
        $this->assertSame('First', $result[0]);
        $this->assertSame(['joe', 'doe'], $result[1]);
    }

    public function testResolveDependenciesWithExceptionForNonExistentClass(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Not instantiable exception: "Failed to instantiate component or class: "NonExistentClass"."',
        );

        $this->factory->resolveDependencies([Instance::of('NonExistentClass')]);
    }

    public function testValidateDependenciesWithInvalidConfigExceptionForMixedIndexing(): void
    {
        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage(
            'Dependencies indexed by name and by position in the same array are not allowed.',
        );

        $this->factory->create(
            Stub\Bar::class,
            [
                '__construct()' => [
                    'definitionClass' => new Stub\DefinitionClass(),
                    '1' => ['a' => 1],
                ],
            ],
        );
    }
}
