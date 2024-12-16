<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di;

use ArrayIterator;
use DateTime;
use PHPPress\Di\Container;
use PHPPress\Di\Definition\Instance;
use PHPPress\Exception\{InvalidArgument, InvalidDefinition};
use PHPPress\Factory\Exception\NotInstantiable;
use PHPUnit\Framework\Attributes\Group;
use Psr\Container\ContainerInterface;
use stdClass;
use Throwable;

/**
 * Test case for the {@see Container} class for invokable class handling in the dependency injection.
 *
 * Tests container's capabilities for:
 * - Auto-wiring invokable classes
 * - Handling default values.
 * - Handling various parameter types (scalar, compound, union, intersection).
 * - Managing variadic arguments.
 * - Processing named and indexed parameters.
 * - Supporting singleton and definition-based instantiation.
 * - Validating parameter types and requirements.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('di')]
final class InvokableTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testAutoWiredUsingDefinition(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkTwo::class,
                'instance' => [
                    '__class' => Stub\Invokable::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertFalse($container->hasSingleton('instance'));
        $this->assertSame('Mark Two', $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs..
     */
    public function testAutoWiredUsingSingleton(): void
    {
        $container = $this->createContainer(
            singletons: [
                Stub\EngineInterface::class => Stub\EngineMarkTwo::class,
                'instance' => Stub\Invokable::class,
            ],
        );

        $instance = $container->get('instance');

        $this->assertTrue($container->hasSingleton('instance'));
        $this->assertSame('Mark Two', $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testBuiltInPHPClassUsingInstantiableClass(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\InvokableBuiltInPHPClass::class);

        $this->assertInstanceOf(DateTime::class, $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testBuiltInPHPClassUsingInstantiableClassAndIndexedParameters(): void
    {
        $dateTime = new DateTime('2024-01-01');
        $container = $this->createContainer(
            [
                Stub\InvokableBuiltInPHPClass::class => [
                    '__invoke()' => [
                        $dateTime,
                    ],
                ],
            ],
        );

        /** @var DateTime $instance */
        $instance = $container->get(Stub\InvokableBuiltInPHPClass::class);

        $this->assertInstanceOf(DateTime::class, $instance);
        $this->assertSame('2024-01-01', $instance->format('Y-m-d'));
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testBuiltInPHPClassUsingInstantiableClassAndNamedParameters(): void
    {
        $dateTime = new DateTime('2024-01-01');
        $container = $this->createContainer(
            [
                Stub\InvokableBuiltInPHPClass::class => [
                    '__invoke()' => [
                        'dateTime' => $dateTime,
                    ],
                ],
            ],
        );

        /** @var DateTime $instance */
        $instance = $container->get(Stub\InvokableBuiltInPHPClass::class);

        $this->assertInstanceOf(DateTime::class, $instance);
        $this->assertSame('2024-01-01', $instance->format('Y-m-d'));
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testBuiltInPHPClassUsingNotInstantiableClass(): void
    {
        $arrayIterator = new ArrayIterator();

        $container = $this->createContainer(
            [
                Stub\InvokableBuiltInPHPClassOptional::class => [
                    '__invoke()' => [
                        $arrayIterator,
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\InvokableBuiltInPHPClassOptional::class);

        $this->assertSame(['iterator' => $arrayIterator], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testBuiltInPHPClassUsingNotInstantiableClassAndOptionalArguments(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\InvokableBuiltInPHPClassOptional::class);

        $this->assertSame(['iterator' => null], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefaultValueArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableDefaultValue::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['class' => 'InvokableDefaultValue', 'engine' => null], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefaultValueArgumentsUsingAutoWired(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                'instance' => [
                    '__class' => Stub\InvokableDefaultValue::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['class' => 'InvokableDefaultValue', 'engine' => 'Mark One'], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingArrayCallable(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    Stub\InstanceFactory::class,
                    'create',
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\Instance::class, $instance);
        $this->assertSame(42, $instance->getA());
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingArrayObjectCallable(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    new Stub\InvokablePSRContainer(),
                    '__invoke',
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(ContainerInterface::class, $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingClosure(): void
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
        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance->getEngineCar()->getEngine());
        $this->assertSame('Mark One', $instance->getEngineCar()->getEngineName());
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingClosureWithPSRContainerInterfaceClass(): void
    {
        $container = $this->createContainer(
            [
                'instance' => static function (ContainerInterface $c): Stub\EngineCarTunning {
                    $engineInterface = $c->get(Stub\EngineInterface::class);
                    $engineCar = new Stub\EngineCar($engineInterface);

                    return new Stub\EngineCarTunning($engineCar);
                },
                Stub\EngineInterface::class => Stub\EngineMarkTwo::class,
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\EngineCarTunning::class, $instance);
        $this->assertInstanceOf(Stub\EngineMarkTwo::class, $instance->getEngineCar()->getEngine());
        $this->assertSame('Mark Two', $instance->getEngineCar()->getEngineName());
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingIndexedParameters(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\Invokable::class,
                    '__invoke()' => [
                        new Stub\EngineMarkTwo(),
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame('Mark Two', $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingIndexedParametersAndCompoundTypeArguments(): void
    {
        $object = new stdClass();
        $callable = static fn() => 'callable';

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableCompoundType::class,
                    '__invoke()' => [
                        [
                            1,
                            2,
                            3,
                        ],
                        $callable,
                        $object,
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['array' => [1, 2, 3], 'callable' => $callable, 'object' => $object], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingIndexedParametersAndInstanceClassArguments(): void
    {
        $container = $this->createContainer(
            [
                'engine-one' => Stub\EngineMarkOne::class,
                'instance' => [
                    '__class' => Stub\Invokable::class,
                    '__invoke()' => [
                        Instance::of('engine-one'),
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame('Mark One', $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingIndexedParametersAndScalarTypeArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableScalarType::class,
                    '__invoke()' => [
                        false,
                        100,
                        2.30,
                        'scalar',
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['bool' => false, 'int' => 100, 'float' => 2.30, 'string' => 'scalar'], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingIndexedParametersAndSeveralArguments(): void
    {
        $callable = static fn(): string => 'callable';
        $object = new stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableSeveralArguments::class,
                    '__invoke()' => [
                        [
                            1,
                            2,
                            3,
                        ],
                        $callable,
                        $object,
                        'InvokableVariadicSeveralArguments',
                        new Stub\EngineMarkTwo(),
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(
            [
                'array' => [
                    1,
                    2,
                    3,
                ],
                'callable' => $callable,
                'object' => $object,
                'string' => 'InvokableVariadicSeveralArguments',
                'engine' => 'Mark Two',
            ],
            $instance,
        );
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingIndexedParametersAndWithoutTypeHintArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableWithoutTypeHint::class,
                    '__invoke()' => [
                        42,
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(42, $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingNamedParameters(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\Invokable::class,
                    '__invoke()' => [
                        'engine' => new Stub\EngineMarkOne(),
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame('Mark One', $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingNamedParametersAndCompoundTypeArguments(): void
    {
        $object = new stdClass();
        $callable = static fn() => 'callable';

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableCompoundType::class,
                    '__invoke()' => [
                        'array' => [
                            1,
                            2,
                            3,
                        ],
                        'callable' => $callable,
                        'object' => $object,
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['array' => [1, 2, 3], 'callable' => $callable, 'object' => $object], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingNamedParametersAndInstanceClassArguments(): void
    {
        $container = $this->createContainer(
            [
                'engine-one' => Stub\EngineMarkOne::class,
                'instance' => [
                    '__class' => Stub\Invokable::class,
                    '__invoke()' => [
                        'engine' => Instance::of('engine-one'),
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame('Mark One', $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingNamedParametersAndScalarTypeArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableScalarType::class,
                    '__invoke()' => [
                        'bool' => false,
                        'int' => 100,
                        'float' => 2.30,
                        'string' => 'scalar',
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['bool' => false, 'int' => 100, 'float' => 2.30, 'string' => 'scalar'], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingNamedParametersAndSeveralArguments(): void
    {
        $callable = static fn(): string => 'callable';
        $object = new stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableSeveralArguments::class,
                    '__invoke()' => [
                        'array' => [
                            1,
                            2,
                            3,
                        ],
                        'callable' => $callable,
                        'object' => $object,
                        'string' => 'InvokableVariadicSeveralArguments',
                        'engine' => new Stub\EngineMarkTwo(),
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(
            [
                'array' => [
                    1,
                    2,
                    3,
                ],
                'callable' => $callable,
                'object' => $object,
                'string' => 'InvokableVariadicSeveralArguments',
                'engine' => 'Mark Two',
            ],
            $instance,
        );
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingNamedParametersAndSeveralArgumentsDisordered(): void
    {
        $callable = static fn(): string => 'callable';
        $object = new stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableSeveralArguments::class,
                    '__invoke()' => [
                        'engine' => new Stub\EngineMarkTwo(),
                        'callable' => $callable,
                        'array' => [
                            1,
                            2,
                            3,
                        ],
                        'object' => $object,
                        'string' => 'InvokableVariadicSeveralArguments',
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(
            [
                'array' => [
                    1,
                    2,
                    3,
                ],
                'callable' => $callable,
                'object' => $object,
                'string' => 'InvokableVariadicSeveralArguments',
                'engine' => 'Mark Two',
            ],
            $instance,
        );
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testDefinitionUsingNamedParametersAndWithoutTypeHintArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableWithoutTypeHint::class,
                    '__invoke()' => [
                        'value' => 42,
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(42, $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testFailsForDefinitionUsingClosureWithMissingRequiredParameter(): void
    {
        $container = $this->createContainer(
            [
                'instance' => static fn(string $requiredParam): string => $requiredParam,
            ],
        );

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage('Invalid definition: "Missing required parameter "requiredParam" when calling "{closure:PHPPress\Tests\Di\InvokableTest::testFailsForDefinitionUsingClosureWithMissingRequiredParameter():721}"."');

        $container->get('instance');
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testFailsForInvalidArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\Invokable::class,
                    '__invoke()' => [
                        42,
                    ],
                ],
            ],
        );

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage(
            'Invalid argument: "PHPPress\Tests\Di\Stub\Invokable::__invoke(): Argument #1 ($engine) must be of type PHPPress\Tests\Di\Stub\EngineInterface, int given',
        );

        $container->get('instance');
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testFailsForInvalidArgumentsFormat(): void
    {
        $container = $this->createContainer(
            [
                Stub\InvokableSeveralArguments::class => [
                    '__invoke()' => [
                        [
                            1,
                            2,
                            3,
                        ],
                        'callable' => static fn(): string => 'callable',
                        'object' => new stdClass(),
                        'engine' => new Stub\EngineMarkOne(),
                    ],
                ],
            ],
        );

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "Dependencies indexed by name and by position in the same array are not allowed.',
        );

        $container->get(Stub\InvokableSeveralArguments::class);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testFailsForInvalidArgumentsMissingRequired(): void
    {
        $container = $this->createContainer();

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "Missing required parameter "array" when calling "__invoke"."',
        );

        $container->get(Stub\InvokableSeveralArguments::class);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testFailsForInvalidArgumentsUnboundDependencies(): void
    {
        $container = $this->createContainer();

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "Missing required parameter "firstDependency" when calling "__invoke"."',
        );

        $container->get(Stub\InvokableMultipleDependencies::class);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testFailsForIntersectionTypeMissingRequired(): void
    {
        $container = $this->createContainer();

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "Missing required parameter "engine" when calling "__invoke"."',
        );

        $container->get(Stub\InvokableIntersectionType::class);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testFailsForNonExistentClass(): void
    {
        $container = $this->createContainer();

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "Missing required parameter "unknownClass" when calling "__invoke"."',
        );

        $container->get(Stub\InvokableUnknownClass::class);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testIntersectionTypeUsingDefinition(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => [
                    '__class' => Stub\EngineMarkTwo::class,
                    'setColor()' => [
                        'blue',
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\InvokableIntersectionType::class);

        $this->assertFalse($container->hasSingleton(Stub\EngineInterface::class));
        $this->assertSame('blue', $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testIntersectionTypeUsingSingleton(): void
    {
        $container = $this->createContainer(
            singletons: [
                Stub\EngineInterface::class => [
                    '__class' => Stub\EngineMarkTwo::class,
                    'setColor()' => [
                        'blue',
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\InvokableIntersectionType::class);

        $this->assertTrue($container->hasSingleton(Stub\EngineInterface::class));
        $this->assertSame('blue', $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testMultipleDependencies1(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                Stub\InstanceInterface::class => Stub\Instance::class,
            ],
        );

        $instance = $container->get(Stub\InvokableMultipleDependencies::class);

        $this->assertInstanceOf(Stub\InvokableMultipleDependencies::class, $instance);

        $actions = $instance->performActions();

        $this->assertSame('Mark One', $actions['first']);
        $this->assertSame(0, $actions['second']);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testPSRContainerInterfaceArgument(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\InvokablePSRContainer::class);

        $this->assertInstanceOf(ContainerInterface::class, $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testRetrievesDefaultValueForOptionalArguments(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\InvokableOptional::class);

        $this->assertNull($instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testRetrievesDefaultValueForOptionalArgumentsUsingDefinition(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                'instance' => [
                    '__class' => Stub\InvokableOptional::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertFalse($container->hasSingleton('instance'));
        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance);
        $this->assertSame('Mark One', $instance->getName());
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testRetrievesDefaultValueForOptionalArgumentsUsingSingleton(): void
    {
        $container = $this->createContainer(
            singletons: [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                'instance' => [
                    '__class' => Stub\InvokableOptional::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertTrue($container->hasSingleton('instance'));
        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance);
        $this->assertSame('Mark One', $instance->getName());
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testUnionTypeUsingDefinition(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                'instance' => Stub\InvokableUnionType::class,
            ],
        );

        $instance = $container->get('instance');

        $this->assertFalse($container->hasSingleton('instance'));
        $this->assertSame('Mark One', $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testUnionTypeUsingSingleton(): void
    {
        $container = $this->createContainer(
            singletons: [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                'instance' => Stub\InvokableUnionType::class,
            ],
        );

        $instance = $container->get('instance');

        $this->assertTrue($container->hasSingleton('instance'));
        $this->assertSame('Mark One', $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingAutoWiredDefinition(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkTwo::class,
                'instance' => [
                    '__class' => Stub\InvokableVariadic::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertFalse($container->hasSingleton('instance'));
        $this->assertSame(['variadic' => ['Mark Two']], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingAutoWiredSingleton(): void
    {
        $container = $this->createContainer(
            singletons: [
                Stub\EngineInterface::class => Stub\EngineMarkTwo::class,
                'instance' => [
                    '__class' => Stub\InvokableVariadic::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertTrue($container->hasSingleton('instance'));
        $this->assertSame(['variadic' => ['Mark Two']], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicDefinitionWithIndexedParameters(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableVariadic::class,
                    '__invoke()' => [
                        [
                            new Stub\EngineMarkOne(),
                            new Stub\EngineMarkTwo(),
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['variadic' => ['Mark One', 'Mark Two']], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingDefinitionWithIndexedParametersAndCompoundTypeArguments(): void
    {
        $object = new stdClass();
        $callable = static fn() => 'callable';

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableVariadicCompoundType::class,
                    '__invoke()' => [
                        [
                            [
                                false,
                                true,
                            ],
                            $object,
                            $callable,
                            null,
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['variadic' => [[false, true], $object, $callable, null]], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingDefinitionWithIndexedParametersAndInstanceClassArguments(): void
    {
        $container = $this->createContainer(
            [
                'engine-one' => Stub\EngineMarkOne::class,
                'engine-two' => Stub\EngineMarkTwo::class,
                'instance' => [
                    '__class' => Stub\InvokableVariadic::class,
                    '__invoke()' => [
                        [
                            Instance::of('engine-one'),
                            Instance::of('engine-two'),
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['variadic' => ['Mark One', 'Mark Two']], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingDefinitionWithIndexedParametersAndScalarTypeArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableVariadicScalarType::class,
                    '__invoke()' => [
                        [
                            false,
                            100,
                            2.30,
                            'variadic',
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['variadic' => [false, 100, 2.30, 'variadic']], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingDefinitionWithIndexedParametersAndSeveralArguments(): void
    {
        $callable = static fn(): string => 'callable';
        $object = new stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableVariadicSeveralArguments::class,
                    '__invoke()' => [
                        [
                            1,
                            2,
                            3,
                        ],
                        $callable,
                        $object,
                        'InvokableVariadicSeveralArguments',
                        [
                            new Stub\EngineMarkOne(),
                            new Stub\EngineMarkTwo(),
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(
            [
                'array' => [
                    1,
                    2,
                    3,
                ],
                'callable' => $callable,
                'object' => $object,
                'string' => 'InvokableVariadicSeveralArguments',
                'variadic' => [
                    'Mark One',
                    'Mark Two',
                ],
            ],
            $instance,
        );
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingDefinitionWithIndexedParametersAndWithoutTypeHintArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableVariadicWithoutTypeHint::class,
                    '__invoke()' => [
                        [
                            false,
                            100,
                            2.30,
                            'variadic',
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['variadic' => [false, 100, 2.30, 'variadic']], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingDefinitionWithNamedParameters(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableVariadic::class,
                    '__invoke()' => [
                        'variadic' => [
                            new Stub\EngineMarkOne(),
                            new Stub\EngineMarkTwo(),
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['variadic' => ['Mark One', 'Mark Two']], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingDefinitionWithNamedParametersAndCompoundTypeArguments(): void
    {
        $object = new stdClass();
        $callable = static fn() => 'callable';

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableVariadicCompoundType::class,
                    '__invoke()' => [
                        'variadic' => [
                            [
                                false,
                                true,
                            ],
                            $object,
                            $callable,
                            null,
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(
            [
                'variadic' => [
                    [
                        false,
                        true,
                    ],
                    $object,
                    $callable,
                    null,
                ],
            ],
            $instance,
        );
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingDefinitionWithNamedParametersAndDefaultValueArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableVariadicDefaultValue::class,
                    '__invoke()' => [
                        'variadic' => [
                            new Stub\EngineMarkOne(),
                            new Stub\EngineMarkTwo(),
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(
            [
                'class' => 'InvokableVariadicDefaultValue',
                'variadic' => [
                    'Mark One',
                    'Mark Two',
                ],
            ],
            $instance,
        );
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingDefinitionWithNamedParametersAndInstanceClassArguments(): void
    {
        $container = $this->createContainer(
            [
                'engine-one' => Stub\EngineMarkOne::class,
                'engine-two' => Stub\EngineMarkTwo::class,
                'instance' => [
                    '__class' => Stub\InvokableVariadic::class,
                    '__invoke()' => [
                        'variadic' => [
                            Instance::of('engine-one'),
                            Instance::of('engine-two'),
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['variadic' => ['Mark One', 'Mark Two']], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingDefinitionWithNamedParametersAndScalarTypeArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableVariadicScalarType::class,
                    '__invoke()' => [
                        'variadic' => [
                            false,
                            100,
                            2.30,
                            'variadic',
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['variadic' => [false, 100, 2.30, 'variadic']], $instance);
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingDefinitionWithNamedParametersAndSeveralArguments(): void
    {
        $callable = static fn(): string => 'callable';
        $object = new stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableVariadicSeveralArguments::class,
                    '__invoke()' => [
                        'array' => [
                            1,
                            2,
                            3,
                        ],
                        'callable' => $callable,
                        'object' => $object,
                        'string' => 'InvokableVariadicSeveralArguments',
                        'variadic' => [
                            new Stub\EngineMarkOne(),
                            new Stub\EngineMarkTwo(),
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(
            [
                'array' => [
                    1,
                    2,
                    3,
                ],
                'callable' => $callable,
                'object' => $object,
                'string' => 'InvokableVariadicSeveralArguments',
                'variadic' => [
                    'Mark One',
                    'Mark Two',
                ],
            ],
            $instance,
        );
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingDefinitionWithNamedParametersAndSeveralArgumentsDisordered(): void
    {
        $callable = static fn(): string => 'callable';
        $object = new stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableVariadicSeveralArguments::class,
                    '__invoke()' => [
                        'variadic' => [
                            new Stub\EngineMarkOne(),
                            new Stub\EngineMarkTwo(),
                        ],
                        'callable' => $callable,
                        'array' => [
                            1,
                            2,
                            3,
                        ],
                        'object' => $object,
                        'string' => 'InvokableVariadicSeveralArguments',
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(
            [
                'array' => [
                    1,
                    2,
                    3,
                ],
                'callable' => $callable,
                'object' => $object,
                'string' => 'InvokableVariadicSeveralArguments',
                'variadic' => [
                    'Mark One',
                    'Mark Two',
                ],
            ],
            $instance,
        );
    }

    /**
     * @throws InvalidDefinition If the definition is invalid.
     * @throws NotInstantiable If the class is not instantiable.
     * @throws Throwable If an error occurs.
     */
    public function testVariadicUsingDefinitionWithNamedParametersAndWithoutTypeHintArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokableVariadicWithoutTypeHint::class,
                    '__invoke()' => [
                        'variadic' => [
                            false,
                            100,
                            2.30,
                            'variadic',
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['variadic' => [false, 100, 2.30, 'variadic']], $instance);
    }

    /**
     * @throws InvalidDefinition When the definition is invalid.
     */
    private function createContainer($definitions = [], $singletons = []): Container
    {
        return new Container($definitions, $singletons);
    }
}
