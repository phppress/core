<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di;

use ArgumentCountError;
use InvalidArgumentException;
use TypeError;
use PHPPress\Di\Exception\NotInstantiable;
use PHPPress\Di\{Container, Instance};
use PHPPress\Exception\InvalidConfig;
use PHPPress\Tests\Provider\ContainerProvider;
use PHPUnit\Framework\Attributes\{DataProviderExternal, Group};

/**
 * Test case for the Container class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('di')]
final class ContainerTest extends \PHPUnit\Framework\TestCase
{
    #[DataProviderExternal(ContainerProvider::class, 'has')]
    public function testHas(bool $expected, $id): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
            ],
        );

        $this->assertSame($expected, $container->has($id));
    }

    public function testHasSingleton(): void
    {
        $container = $this->createContainer(
            singletons: [
                Stub\ClassInstance::class,
            ],
        );

        $this->assertTrue($container->hasSingleton(Stub\ClassInstance::class));
    }

    public function testHasSingletonUsingCheckInstanceWithTrueValue(): void
    {
        $container = $this->createContainer(
            singletons: [
                'instance' => Stub\ClassInstance::class,
            ],
        );

        $this->assertInstanceOf(Stub\ClassInstance::class, $container->get('instance'));
        $this->assertTrue($container->hasSingleton('instance', true));
    }

    public function testHasSingletonUsingCheckInstanceWithTrueValueAndNotInstantiableClass(): void
    {
        $container = $this->createContainer(
            singletons: [
                'instance' => Stub\ClassInstance::class,
            ],
        );

        $this->assertFalse($container->hasSingleton('instance', true));
    }

    public function testHasSingletonUsingClassAliases(): void
    {
        $container = $this->createContainer(
            singletons: [
                'instance' => Stub\ClassInstance::class,
            ],
        );

        $this->assertTrue($container->hasSingleton('instance'));
    }

    public function testHasSingletonUsingClassInstanceReference(): void
    {
        $container = $this->createContainer(
            singletons: [
                'instance' => Instance::of(Stub\ClassInstance::class),
            ],
        );

        $this->assertTrue($container->hasSingleton('instance'));
    }

    public function testInstantiation(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\ClassInstance::class);

        $this->assertInstanceOf(Stub\ClassInstance::class, $instance);
        $this->assertSame(0, $instance->getA());
        $this->assertSame(0, $instance->getB());
    }

    public function testInstantiationFailsForNonExistentClass(): void
    {
        $container = $this->createContainer();

        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage(
            'Not instantiable exception: "Failed to instantiate component or class: "NonExistentClass"."',
        );

        $container->get(\NonExistentClass::class);
    }

    public function testInstantiationFailsWhenValidateEmptyStringConstructorArgument(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithStrictConstructor::class => ['__construct()' => ['']],
            ],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be a non-empty string with at least 3 characters');

        $container->get(Stub\ClassWithStrictConstructor::class);
    }

    public function testInstantiationFailsWhenValidateShortStringConstructorArgument(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithStrictConstructor::class => ['__construct()' => ['ab']],
            ],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value must be a non-empty string with at least 3 characters');

        $container->get(Stub\ClassWithStrictConstructor::class);
    }

    public function testInstantiationFailsWithConstructorInvalidArguments(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorNullableArgument::class => ['__construct()' => [new \stdClass()]],
            ],
        );

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            'PHPPress\Tests\Di\Stub\ClassWithConstructorNullableArgument::__construct(): Argument #1 ($car) must be of type ?PHPPress\Tests\Di\Stub\EngineCar, stdClass given',
        );

        $container->get(Stub\ClassWithConstructorNullableArgument::class);
    }

    public function testInstantiationFailsWithConstructorInvalidArgumentsFormat(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorNullableArgument::class => ['__construct()' => ['car' => null, 1]],
            ],
        );

        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage(
            'Invalid configuration: "Dependencies indexed by name and by position in the same array are not allowed."',
        );

        $container->get(Stub\ClassWithConstructorNullableArgument::class);
    }

    public function testInstantiationFailsWithConstructorMissingArguments(): void
    {
        $container = $this->createContainer();

        $this->expectException(ArgumentCountError::class);
        $this->expectExceptionMessage(
            'Too few arguments to function PHPPress\Tests\Di\Stub\ClassWithStrictConstructor::__construct(), 0 passed and exactly 1 expected',
        );

        $container->get(Stub\ClassWithStrictConstructor::class);
    }

    public function testInstantiationFailsWithConstructorMissingArgumentsRequired(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorNullableArgument::class => ['__construct()' => []],
            ],
        );

        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage(
            'Missing required parameter "car" when instantiating "PHPPress\Tests\Di\Stub\ClassWithConstructorNullableArgument"."',
        );

        $container->get(Stub\ClassWithConstructorNullableArgument::class);
    }

    public function testInstantiationFailsWithInvalidDefinitionArrayValue(): void
    {
        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage(
            'Invalid configuration: "A class definition requires a "__class" or "class" member."',
        );

        $this->createContainer(
            [
                'instance' => ['invalid'],
            ],
        );
    }

    public function testInstantiationFailsWithInvalidDefinitionIntegerValue(): void
    {
        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage('Invalid configuration: "Unsupported definition type for "integer"."');

        new Container(['instance' => 42]);
    }

    public function testInstantiationFailsWithInvalidDefinitionStringValue(): void
    {
        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage('Invalid configuration: "Invalid definition for "instance": invalid"');

        $this->createContainer(
            [
                'instance' => 'invalid',
            ],
        );
    }

    public function testInstantiationFailsWithUnboundDependencies(): void
    {
        $container = $this->createContainer();

        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage(
            'Missing required parameter "firstDependency" when instantiating "PHPPress\Tests\Di\Stub\ClassWithConstructorMultipleDependencies"."',
        );

        $container->get(Stub\ClassWithConstructorMultipleDependencies::class);
    }

    public function testInstantiationRetrievesDefaultValueForOptionalArguments(): void
    {
        $container = $this->createContainer();

        $this->assertTrue($container->has(Stub\ClassWithConstructorNullValue::class));

        $instance = $container->get(Stub\ClassWithConstructorNullValue::class);

        $this->assertNull($instance->getCar());
    }

    public function testInstantiationSucceedsWithConstructorArguments(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorNullableArgument::class => [
                    '__construct()' => [new Stub\EngineCar(new Stub\EngineMarkOne())],
                ],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorNullableArgument::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorNullableArgument::class, $instance);
        $this->assertInstanceOf(Stub\EngineCar::class, $instance->getCar());
    }

    public function testInstantiationSucceedsWithConstructorArgumentsUsingDefaultValue(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\ClassWithConstructorDefaultValue::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorDefaultValue::class, $instance);
        $this->assertSame('default', $instance->getValue());
    }

    public function testInstantiationSucceedsWithConstructorArgumentsUsingDefinitionInterface(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
            ],
        );

        $this->assertTrue($container->has(Stub\ClassWithConstructorNullableArgument::class));

        $instance = $container->get(Stub\ClassWithConstructorNullableArgument::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorNullableArgument::class, $instance);
        $this->assertInstanceOf(Stub\EngineCar::class, $instance->getCar());
    }

    public function testInstantiationSucceedsWithConstructorArgumentsUsingUnionType(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorUnionType::class => ['__construct()' => ['value' => 'a']],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorUnionType::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorUnionType::class, $instance);
        $this->assertSame('a', $instance->getValue());

        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorUnionType::class => ['__construct()' => ['value' => 1]],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorUnionType::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorUnionType::class, $instance);
        $this->assertSame(1, $instance->getValue());

        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorUnionType::class => ['__construct()' => ['value' => 2.3]],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorUnionType::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorUnionType::class, $instance);
        $this->assertSame(2.3, $instance->getValue());

        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorUnionType::class => ['__construct()' => ['value' => true]],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorUnionType::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorUnionType::class, $instance);
        $this->assertTrue($instance->getValue());

        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorUnionType::class => ['__construct()' => ['value' => null]],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorUnionType::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorUnionType::class, $instance);
        $this->assertNull($instance->getValue());
    }

    public function testInstantiationSucceedsWithConstructorArgumentsUsingOverriddenDefaultValue(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorDefaultValue::class => ['__construct()' => ['value' => 'custom']],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorDefaultValue::class);

        $this->assertSame('custom', $instance->getValue());
    }

    public function testInstantiationSucceedsWithComplexTypeConstructor(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\ClassWithConstructorDateTime::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorDateTime::class, $instance);
        $this->assertInstanceOf(\DateTime::class, $instance->getDateTime());
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}/', $instance->getFormattedDate());
    }

    public function testInstantiationSucceedsWithDependencyInjection(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\ClassWithConstructor::class);

        $this->assertInstanceOf(Stub\ClassWithConstructor::class, $instance);
        $this->assertInstanceOf(Stub\ClassInstance::class, $instance->getDefinitionClass());
    }

    public function testInstantiationSucceedsWithInterfaceInConstructor(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorInterface::class => [
                    '__construct()' => [new Stub\EngineMarkOne()],
                ],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorInterface::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorInterface::class, $instance);
        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance->getInterface());

        $actions = $instance->performActions();

        $this->assertSame('Mark One', $actions['name']);
    }

    public function testInstantiationSucceedsWithMultipleDependencies(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                Stub\ClassInterface::class => Stub\ClassInstance::class,
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorMultipleDependencies::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorMultipleDependencies::class, $instance);
        $this->assertInstanceOf(Stub\EngineInterface::class, $instance->getFirstDependency());
        $this->assertInstanceOf(Stub\ClassInterface::class, $instance->getSecondDependency());

        $actions = $instance->performActions();

        $this->assertSame('Mark One', $actions['first']);
        $this->assertSame(0, $actions['second']);
    }

    public function testInstantiationSucceedsWithVariadicConstructorArguments(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorVaradic::class => ['__construct()' => [200, [true], 'a', 'b', 'c']],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorVaradic::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorVaradic::class, $instance);
        $this->assertsame(200, $instance->getKey());
        $this->assertSame([true], $instance->getValueArray());
        $this->assertSame(['a', 'b', 'c'], $instance->getValueVaradic());
    }

    public function testInstantiationSucceedsWithVariadicMixedArguments(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorVaradicMixedValue::class => ['__construct()' => [1, 'a', 2.3]],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorVaradicMixedValue::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorVaradicMixedValue::class, $instance);
        $this->assertSame([1, 'a', 2.3], $instance->getValue());
    }

    public function testInstantiationSucceedsWithVariadicUnionTypeArguments(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorUnionTypeVaradic::class => ['__construct()' => [true, 1, 'a']],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorUnionTypeVaradic::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorUnionTypeVaradic::class, $instance);
        $this->assertSame([true, 1, 'a'], $instance->getValue());
    }

    public function testInstantiationSucceedsWithVariadicWithoutTypeHint(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorVaradicNotTypeHintValue::class => ['__construct()' => [1, 'a', 2.3]],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorVaradicNotTypeHintValue::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorVaradicNotTypeHintValue::class, $instance);
        $this->assertSame([1, 'a', 2.3], $instance->getValue());
    }

    public function testInstantiationUsingAutomaticWiring(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
            ],
        );

        $instance = $container->get(Stub\EngineInterface::class);

        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance);

        $instance = $container->get(Stub\EngineCar::class);

        $this->assertInstanceOf(Stub\EngineCar::class, $instance);
        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance->getEngine());
        $this->assertSame('Mark One', $instance->getEngineName());
    }

    public function testInstantiationUsingCallable(): void
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

    public function testInstantiationUsingCallableWithInterfaceClass(): void
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

    public function testInstantiationUsingClassAliases(): void
    {
        $container = $this->createContainer(
            [
                'instance' => Stub\ClassInstance::class,
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\ClassInstance::class, $instance);
        $this->assertSame(0, $instance->getA());
        $this->assertSame(0, $instance->getB());
    }

    public function testInstantiationUsingClassIndirectly(): void
    {
        $container = $this->createContainer(
            [
                'instance' => Stub\ClassInstance::class,
                Stub\ClassInstance::class => ['setA()' => [42]],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\ClassInstance::class, $instance);
        $this->assertSame(42, $instance->getA());
    }

    public function testInstantiationUsingClassInstanceReference(): void
    {
        $container = $this->createContainer(
            [
                'instance' => Instance::of(Stub\ClassInstance::class),
            ],
        );

        $this->assertInstanceOf(Stub\ClassInstance::class, $container->get('instance'));
    }

    public function testInstantiationUsingDefinitions(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassInstance::class => [
                    'setA()' => [42],
                    'setB()' => [142],
                    'c' => 242,
                ],
            ],
        );

        $instance = $container->get(Stub\ClassInstance::class);

        $this->assertInstanceOf(Stub\ClassInstance::class, $instance);
        $this->assertSame(42, $instance->getA());
        $this->assertSame(142, $instance->getB());
        $this->assertSame(242, $instance->c);
    }

    public function testInstantiationUsingFullWiringResolvesCorrectDependencies(): void
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

    public function testInstantiationUsingInterfaceBinding(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassInterface::class => Stub\ClassInstance::class,
            ],
        );

        $instance = $container->get(Stub\ClassInterface::class);

        $this->assertInstanceOf(Stub\ClassInstance::class, $instance);
        $this->assertSame(0, $instance->getA());
        $this->assertSame(0, $instance->getB());
    }

    public function testInstantiationUsingHookClass(): void
    {
        $container = $this->createContainer(
            [
                'hook' => [
                    '__class' => Stub\ClassHook::class,
                    'firstName' => 'john',
                    'lastName' => 'doe',
                ],
            ],
        );

        $instance = $container->get('hook');

        $this->assertInstanceOf(Stub\ClassHook::class, $instance);
        $this->assertSame('John Doe', $instance->fullName);
    }

    public function testInstantiationUsingImmutableMethod(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassInstance::class => [
                    'withD()' => [1000],
                ],
            ],
        );

        $instance = $container->get(Stub\ClassInstance::class);

        $this->assertInstanceOf(Stub\ClassInstance::class, $instance);
        $this->assertSame(1000, $instance->d);
    }

    public function testInstantiationUsingParameterClassInstance(): void
    {
        $container = $this->createContainer(
            [
                'engineCarTunning' => [
                    '__class' => Stub\EngineCarTunning::class,
                    '__construct()' => [Instance::of('engineCar')],
                ],
                'engineCar' => [
                    '__class' => Stub\EngineCar::class,
                    '__construct()' => [Instance::of('engine')],
                ],
                'engine' => Stub\EngineMarkOne::class,
            ],
        );

        $instance = $container->get('engineCarTunning');

        $this->assertInstanceOf(Stub\EngineCarTunning::class, $instance);
        $this->assertInstanceOf(Stub\EngineCar::class, $instance->getEngineCar());
        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance->getEngineCar()->getEngine());
        $this->assertSame('Mark One', $instance->getEngineCar()->getEngineName());
    }

    public function testInstantiationUsingWiringByClosure(): void
    {
        $container = $this->createContainer(
            [
                'instance' => static function (): Stub\EngineCarTunning {
                    $engineMarkOne = new Stub\EngineMarkOne();
                    $engineCar = new Stub\EngineCar($engineMarkOne);

                    return new Stub\EngineCarTunning($engineCar);
                },
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\EngineCarTunning::class, $instance);
        $this->assertInstanceOf(Stub\EngineCar::class, $instance->getEngineCar());
        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance->getEngineCar()->getEngine());
        $this->assertSame('Mark One', $instance->getEngineCar()->getEngineName());
    }

    public function testInstantiationUsingWiringByClosureWithContainer(): void
    {
        $container = $this->createContainer(
            [
                'instance' => static function (Container $c): Stub\EngineCarTunning {
                    $engineInterface = $c->get(Stub\EngineInterface::class);
                    $engineCar = new Stub\EngineCar($engineInterface);

                    return new Stub\EngineCarTunning($engineCar);
                },
                Stub\EngineInterface::class => Stub\EngineMarkTwo::class,
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\EngineCarTunning::class, $instance);
        $this->assertInstanceOf(Stub\EngineCar::class, $instance->getEngineCar());
        $this->assertInstanceOf(Stub\EngineMarkTwo::class, $instance->getEngineCar()->getEngine());
        $this->assertSame('Mark Two', $instance->getEngineCar()->getEngineName());
    }

    public function testInstantiationWithConstructorArguments(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorNullableArgument::class => ['__construct()' => [null]],
            ],
        );

        $intance = $container->get(Stub\ClassWithConstructorNullableArgument::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorNullableArgument::class, $intance);
        $this->assertNull($intance->getCar());
    }

    public function testInstantiationWithConstuctorAssociativeArguments(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorNullableArgument::class => ['__construct()' => ['car' => null]],
            ],
        );

        $intance = $container->get(Stub\ClassWithConstructorNullableArgument::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorNullableArgument::class, $intance);
        $this->assertNull($intance->getCar());
    }

    public function testInstantiationWithMergeDependenciesWithAsociativeArrays(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorScalarArguments::class => [
                    '__construct()' => ['a' => 10, 'c' => 20],
                ],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorScalarArguments::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorScalarArguments::class, $instance);
        $this->assertSame(10, $instance->getA());
        $this->assertSame(0, $instance->getB());
        $this->assertSame(20, $instance->getC());
    }

    public function testInstantiationWithMergeDependenciesWithEmptyArrays(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorScalarArguments::class => [
                    '__construct()' => [],
                ],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorScalarArguments::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorScalarArguments::class, $instance);
        $this->assertSame(0, $instance->getA());
        $this->assertSame(0, $instance->getB());
        $this->assertSame(0, $instance->getC());
    }


    public function testInstantiationWithMergeDependenciesWithIndexedArrays(): void
    {
        $container = $this->createContainer(
            [
                Stub\ClassWithConstructorScalarArguments::class => [
                    '__construct()' => [10, 20],
                ],
            ],
        );

        $instance = $container->get(Stub\ClassWithConstructorScalarArguments::class);

        $this->assertInstanceOf(Stub\ClassWithConstructorScalarArguments::class, $instance);
        $this->assertSame(10, $instance->getA());
        $this->assertSame(20, $instance->getB());
        $this->assertSame(0, $instance->getC());
    }

    public function testInstantiationWithReturnsAndStoreSingletonObjectDefinition(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineMarkOne::class,
            ],
            [
                'instance' => new Stub\ClassInstance(),
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\ClassInstance::class, $instance);
        $this->assertSame(0, $instance->getA());
    }

    public function testInstantiationWithReturnsExistingSingletonInstance(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class,
            ],
            [
                Stub\ClassInstance::class,
            ],
        );

        $instanceOne = $container->get(Stub\ClassInstance::class);
        $instanceTwo = $container->get(Stub\ClassInstance::class);

        $this->assertSame($instanceOne, $instanceTwo);
    }

    public function testInstantiationWithReflectionCache(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\ClassWithConstructor::class);

        $this->assertInstanceOf(Stub\ClassWithConstructor::class, $instance);
        $this->assertInstanceOf(Stub\ClassInstance::class, $instance->getDefinitionClass());

        $container->set(Stub\ClassInterface::class, Stub\ClassInstance::class);

        $instance = $container->get(Stub\ClassInterface::class);

        $this->assertInstanceOf(Stub\ClassInstance::class, $instance);
    }

    public function testInvokeableClass(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
            ],
        );

        $instance = $container->get(Stub\ClassInvokeable::class);

        $this->assertInstanceOf(Stub\EngineCar::class, $instance);
    }

    public function testInvokeableClassWithoutTypeHint(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ClassInvokeableWithoutType::class,
                    '__invoke()' => [42],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(42, $instance);
    }

    public function testInvokeableClassWithIntersectionType(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => [
                    '__class' => Stub\EngineMarkTwo::class,
                    'setColor()' => ['blue'],
                ],
            ],
        );

        $instance = $container->get(Stub\ClassInvokeableWithIntersectionType::class);

        $this->assertSame('blue', $instance);
    }

    public function testInvokeableClassWithIntersectionTypeException(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
            ],
        );

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            'PHPPress\Tests\Di\Stub\ClassInvokeableWithIntersectionType::__invoke(): Argument #1 ($engine) must be of type PHPPress\Tests\Di\Stub\EngineInterface&PHPPress\Tests\Di\Stub\EngineColorInterface, PHPPress\Tests\Di\Stub\EngineMarkOne given',
        );

        $container->get(Stub\ClassInvokeableWithIntersectionType::class);
    }

    public function testInvokeableClassWithOptionalArguments(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\ClassInvokeableOptionalArgument::class);

        $this->assertNull($instance);
    }

    public function testInvokeableClassWithOptionalArgumentsUsingDefinitions(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                'instance' => [
                    '__class' => Stub\ClassInvokeableOptionalArgument::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance);
    }

    public function testInvokeableClassWithOptionalArgumentsUsingParametersAsociativeArray(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ClassInvokeableOptionalArgument::class,
                    '__invoke()' => ['engine' => new Stub\EngineMarkOne()],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance);
    }

    public function testInvokeableClassWithOptionalArgumentsUsingParametersListArray(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ClassInvokeableOptionalArgument::class,
                    '__invoke()' => [new Stub\EngineMarkOne()],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance);
    }

    public function testInvokeableClassUsingUnionType(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                'instance' => Stub\ClassInvokeableWithUnionType::class,
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame('Mark One', $instance);
    }

    public function testInvokeableClassUsingVaradic(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ClassInvokeableWithVaradic::class,
                    '__invoke()' => [1, 2, 3],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame([1, 2, 3], $instance);
    }

    public function testInvokeableClassWithParametersAsociativeArray(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ClassInvokeableWithoutType::class,
                    '__invoke()' => ['value' => 42],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(42, $instance);
    }

    public function testInvokeableClassWithParametersListArray(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ClassInvokeableWithoutType::class,
                    '__invoke()' => [42],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(42, $instance);
    }

    public function testMultipleGetCallsCreateDifferentInstances(): void
    {
        $container = $this->createContainer();

        $instanceOne = $container->get(Stub\ClassInstance::class);
        $instanceTwo = $container->get(Stub\ClassInstance::class);

        $this->assertNotSame($instanceOne, $instanceTwo);
    }

    public function testRetrieveContainerDefinitions(): void
    {
        $definitions = [
            'instance' => [
                'class' => Stub\ClassInstance::class,
                'a()' => [42],
            ],
        ];

        $container = $this->createContainer($definitions);

        $expected = $definitions;
        $expected += [\Psr\Container\ContainerInterface::class => ['class' => Container::class]];
        $expected += [Container::class => $container];

        $this->assertSame($expected, $container->getDefinitions());
    }

    public function testSet(): void
    {
        $container = $this->createContainer();

        $container->set('instance', Stub\ClassInstance::class);

        $this->assertTrue($container->has('instance'));
        $this->assertInstanceOf(Stub\ClassInstance::class, $container->get('instance'));
    }

    public function testSetSingleton(): void
    {
        $container = $this->createContainer();

        $container->setSingleton('instance', Stub\ClassInstance::class);

        $this->assertTrue($container->hasSingleton('instance'));
        $this->assertInstanceOf(Stub\ClassInstance::class, $container->get('instance'));
    }

    private function createContainer($definitions = [], $singletons = []): Container
    {
        return new Container($definitions, $singletons);
    }
}
