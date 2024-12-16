<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di;

use ArrayIterator;
use DateTime;
use PHPPress\Di\Container;
use PHPPress\Di\Definition\Instance;
use PHPPress\Exception\{InvalidArgument, InvalidDefinition};
use PHPUnit\Framework\Attributes\Group;
use Psr\Container\ContainerInterface;

/**
 * Test case for the {@see Container} class for invokeable class handling in the dependency injection.
 *
 * Tests container's capabilities for:
 * - Auto-wiring invokeable classes
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
final class InvokeableTest extends \PHPUnit\Framework\TestCase
{
    public function testAutoWiredUsingDefinition(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkTwo::class,
                'instance' => [
                    '__class' => Stub\Invokeable::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertFalse($container->hasSingleton('instance'));
        $this->assertSame('Mark Two', $instance);
    }

    public function testAutoWiredUsingSingleton(): void
    {
        $container = $this->createContainer(
            singletons: [
                Stub\EngineInterface::class => Stub\EngineMarkTwo::class,
                'instance' => Stub\Invokeable::class,
            ],
        );

        $instance = $container->get('instance');

        $this->assertTrue($container->hasSingleton('instance'));
        $this->assertSame('Mark Two', $instance);
    }

    public function testBuiltInPHPClassUsingInstatiableClass(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\InvokeableBuiltInPHPClass::class);

        $this->assertInstanceOf(DateTime::class, $instance);
    }

    public function testBuiltInPHPClassUsingInstatiableClassAndIndexedParameters(): void
    {
        $dateTime = new DateTime('2024-01-01');
        $container = $this->createContainer(
            [
                Stub\InvokeableBuiltInPHPClass::class => [
                    '__invoke()' => [
                        $dateTime,
                    ],
                ],
            ],
        );

        /** @var DateTime $instance */
        $instance = $container->get(Stub\InvokeableBuiltInPHPClass::class);

        $this->assertInstanceOf(Datetime::class, $instance);
        $this->assertSame('2024-01-01', $instance->format('Y-m-d'));
    }

    public function testBuiltInPHPClassUsingInstatiableClassAndNamedParameters(): void
    {
        $dateTime = new DateTime('2024-01-01');
        $container = $this->createContainer(
            [
                Stub\InvokeableBuiltInPHPClass::class => [
                    '__invoke()' => [
                        'dateTime' => $dateTime,
                    ],
                ],
            ],
        );

        /** @var DateTime $instance */
        $instance = $container->get(Stub\InvokeableBuiltInPHPClass::class);

        $this->assertInstanceOf(Datetime::class, $instance);
        $this->assertSame('2024-01-01', $instance->format('Y-m-d'));
    }

    public function testBuiltInPHPClassUsingNotInstatiableClass(): void
    {
        $arrayIterator = new ArrayIterator();

        $container = $this->createContainer(
            [
                Stub\InvokeableBuiltInPHPClassOptional::class => [
                    '__invoke()' => [
                        $arrayIterator,
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\InvokeableBuiltInPHPClassOptional::class);

        $this->assertSame(['iterator' => $arrayIterator], $instance);
    }

    public function testBuiltInPHPClassUsingNotInstatiableClassAndOptionalArguments(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\InvokeableBuiltInPHPClassOptional::class);

        $this->assertSame(['iterator' => null], $instance);
    }

    public function testDefaultValueArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableDefaultValue::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['class' => 'InvokeableDefaultValue', 'engine' => null], $instance);
    }

    public function testDefaultValueArgumentsUsingAutoWired(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                'instance' => [
                    '__class' => Stub\InvokeableDefaultValue::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(['class' => 'InvokeableDefaultValue', 'engine' => 'Mark One'], $instance);
    }

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

    public function testDefinitionUsingArrayObjectCallable(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    new Stub\InvokeablePSRContainer(),
                    '__invoke',
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(ContainerInterface::class, $instance);
    }

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
        $this->assertInstanceOf(Stub\EngineCar::class, $instance->getEngineCar());
        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance->getEngineCar()->getEngine());
        $this->assertSame('Mark One', $instance->getEngineCar()->getEngineName());
    }

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
        $this->assertInstanceOf(Stub\EngineCar::class, $instance->getEngineCar());
        $this->assertInstanceOf(Stub\EngineMarkTwo::class, $instance->getEngineCar()->getEngine());
        $this->assertSame('Mark Two', $instance->getEngineCar()->getEngineName());
    }

    public function testDefinitionUsingIndexedParameters(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\Invokeable::class,
                    '__invoke()' => [
                        new Stub\EngineMarkTwo(),
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame('Mark Two', $instance);
    }

    public function testDefinitionUsingIndexedParametersAndCompundTypeArguments(): void
    {
        $object = new \stdClass();
        $callable = static fn() => 'callable';

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableCompundType::class,
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

    public function testDefinitionUsingIndexedParametersAndInstanceClassArguments(): void
    {
        $container = $this->createContainer(
            [
                'engine-one' => Stub\EngineMarkOne::class,
                'instance' => [
                    '__class' => Stub\Invokeable::class,
                    '__invoke()' => [
                        Instance::of('engine-one'),
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame('Mark One', $instance);
    }

    public function testDefinitionUsingIndexedParametersAndScalarTypeArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableScalarType::class,
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

    public function testDefinitionUsingIndexedParametersAndSeveralArguments(): void
    {
        $callable = static fn(): string => 'callable';
        $object = new \stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableSeveralArguments::class,
                    '__invoke()' => [
                        [
                            1,
                            2,
                            3,
                        ],
                        $callable,
                        $object,
                        'InvokeableVariadicSeveralArguments',
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
                'string' => 'InvokeableVariadicSeveralArguments',
                'engine' => 'Mark Two',
            ],
            $instance,
        );
    }

    public function testDefinitionUsingIndexedParametersAndWithoutTypeHintArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableWithoutTypeHint::class,
                    '__invoke()' => [
                        42,
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(42, $instance);
    }

    public function testDefinitionUsingNamedParameters(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\Invokeable::class,
                    '__invoke()' => [
                        'engine' => new Stub\EngineMarkOne(),
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame('Mark One', $instance);
    }

    public function testDefinitionUsingNamedParametersAndCompundTypeArguments(): void
    {
        $object = new \stdClass();
        $callable = static fn() => 'callable';

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableCompundType::class,
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

    public function testDefinitionUsingNamedParametersAndInstanceClassArguments(): void
    {
        $container = $this->createContainer(
            [
                'engine-one' => Stub\EngineMarkOne::class,
                'instance' => [
                    '__class' => Stub\Invokeable::class,
                    '__invoke()' => [
                        'engine' => Instance::of('engine-one'),
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame('Mark One', $instance);
    }

    public function testDefinitionUsingNamedParametersAndScalarTypeArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableScalarType::class,
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

    public function testDefinitionUsingNamedParametersAndSeveralArguments(): void
    {
        $callable = static fn(): string => 'callable';
        $object = new \stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableSeveralArguments::class,
                    '__invoke()' => [
                        'array' => [
                            1,
                            2,
                            3,
                        ],
                        'callable' => $callable,
                        'object' => $object,
                        'string' => 'InvokeableVariadicSeveralArguments',
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
                'string' => 'InvokeableVariadicSeveralArguments',
                'engine' => 'Mark Two',
            ],
            $instance,
        );
    }

    public function testDefinitionUsingNamedParametersAndSeveralArgumentsDisordered(): void
    {
        $callable = static fn(): string => 'callable';
        $object = new \stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableSeveralArguments::class,
                    '__invoke()' => [
                        'engine' => new Stub\EngineMarkTwo(),
                        'callable' => $callable,
                        'array' => [
                            1,
                            2,
                            3,
                        ],
                        'object' => $object,
                        'string' => 'InvokeableVariadicSeveralArguments',
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
                'string' => 'InvokeableVariadicSeveralArguments',
                'engine' => 'Mark Two',
            ],
            $instance,
        );
    }

    public function testDefinitionUsingNamedParametersAndWithoutTypeHintArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableWithoutTypeHint::class,
                    '__invoke()' => [
                        'value' => 42,
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertSame(42, $instance);
    }

    public function testFailsForDefinitionUsingClosureWithMissingRequiredParameter(): void
    {
        $container = $this->createContainer(
            [
                'instance' => static fn(string $requiredParam): string => $requiredParam,
            ],
        );

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage('Invalid definition: "Missing required parameter "requiredParam" when calling "{closure:PHPPress\Tests\Di\InvokeableTest::testFailsForDefinitionUsingClosureWithMissingRequiredParameter():517}"."');

        $container->get('instance');
    }

    public function testFailsForInvalidArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\Invokeable::class,
                    '__invoke()' => [
                        42,
                    ],
                ],
            ],
        );

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage(
            'Invalid argument: "PHPPress\Tests\Di\Stub\Invokeable::__invoke(): Argument #1 ($engine) must be of type PHPPress\Tests\Di\Stub\EngineInterface, int given',
        );

        $container->get('instance');
    }

    public function testFailsForInvalidArgumentsFormat(): void
    {
        $container = $this->createContainer(
            [
                Stub\InvokeableSeveralArguments::class => [
                    '__invoke()' => [
                        [
                            1,
                            2,
                            3,
                        ],
                        'callable' => static fn(): string => 'callable',
                        'object' => new \stdClass(),
                        'engine' => new Stub\EngineMarkOne(),
                    ],
                ],
            ],
        );

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "Dependencies indexed by name and by position in the same array are not allowed.',
        );

        $container->get(Stub\InvokeableSeveralArguments::class);
    }

    public function testFailsForInvalidArgumentsMissingRequired(): void
    {
        $container = $this->createContainer();

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "Missing required parameter "array" when calling "__invoke"."',
        );

        $container->get(Stub\InvokeableSeveralArguments::class);
    }

    public function testFailsForInvalidArgumentsUnboundDependencies(): void
    {
        $container = $this->createContainer();

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "Missing required parameter "firstDependency" when calling "__invoke"."',
        );

        $container->get(Stub\InvokeableMultipleDependencies::class);
    }

    public function testFailsForIntersectionTypeMissingRequired(): void
    {
        $container = $this->createContainer();

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "Missing required parameter "engine" when calling "__invoke"."',
        );

        $container->get(Stub\InvokeableIntersectionType::class);
    }

    public function testFailsForNonExistentClass(): void
    {
        $container = $this->createContainer();

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "Missing required parameter "unknownClass" when calling "__invoke"."',
        );

        $container->get(Stub\InvokeableUnknownClass::class);
    }

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

        $instance = $container->get(Stub\InvokeableIntersectionType::class);

        $this->assertFalse($container->hasSingleton(Stub\EngineInterface::class));
        $this->assertSame('blue', $instance);
    }

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

        $instance = $container->get(Stub\InvokeableIntersectionType::class);

        $this->assertTrue($container->hasSingleton(Stub\EngineInterface::class));
        $this->assertSame('blue', $instance);
    }

    public function testMultipleDependencies1(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                Stub\InstanceInterface::class => Stub\Instance::class,
            ],
        );

        $instance = $container->get(Stub\InvokeableMultipleDependencies::class);

        $this->assertInstanceOf(Stub\InvokeableMultipleDependencies::class, $instance);
        $this->assertInstanceOf(Stub\EngineInterface::class, $instance->getFirstDependency());
        $this->assertInstanceOf(Stub\InstanceInterface::class, $instance->getSecondDependency());

        $actions = $instance->performActions();

        $this->assertSame('Mark One', $actions['first']);
        $this->assertSame(0, $actions['second']);
    }

    public function testPSRContainerInterfaceArgument(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\InvokeablePSRContainer::class);

        $this->assertInstanceOf(ContainerInterface::class, $instance);
    }

    public function testRetrievesDefaultValueForOptionalArguments(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\InvokeableOptional::class);

        $this->assertNull($instance);
    }

    public function testRetrievesDefaultValueForOptionalArgumentsUsingDefinition(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                'instance' => [
                    '__class' => Stub\InvokeableOptional::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertFalse($container->hasSingleton('instance'));
        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance);
        $this->assertSame('Mark One', $instance->getName());
    }

    public function testRetrievesDefaultValueForOptionalArgumentsUsingSingleton(): void
    {
        $container = $this->createContainer(
            singletons: [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                'instance' => [
                    '__class' => Stub\InvokeableOptional::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertTrue($container->hasSingleton('instance'));
        $this->assertInstanceOf(Stub\EngineMarkOne::class, $instance);
        $this->assertSame('Mark One', $instance->getName());
    }

    public function testUnionTypeUsingDefinition(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                'instance' => Stub\InvokeableUnionType::class,
            ],
        );

        $instance = $container->get('instance');

        $this->assertFalse($container->hasSingleton('instance'));
        $this->assertSame('Mark One', $instance);
    }

    public function testUnionTypeUsingSingleton(): void
    {
        $container = $this->createContainer(
            singletons: [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                'instance' => Stub\InvokeableUnionType::class,
            ],
        );

        $instance = $container->get('instance');

        $this->assertTrue($container->hasSingleton('instance'));
        $this->assertSame('Mark One', $instance);
    }

    public function testVariadicUsingAutoWiredDefinition(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkTwo::class,
                'instance' => [
                    '__class' => Stub\InvokeableVariadic::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertFalse($container->hasSingleton('instance'));
        $this->assertSame(['variadic' => ['Mark Two']], $instance);
    }

    public function testVariadicUsingAutoWiredSingleton(): void
    {
        $container = $this->createContainer(
            singletons: [
                Stub\EngineInterface::class => Stub\EngineMarkTwo::class,
                'instance' => [
                    '__class' => Stub\InvokeableVariadic::class,
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertTrue($container->hasSingleton('instance'));
        $this->assertSame(['variadic' => ['Mark Two']], $instance);
    }

    public function testVariadicDefinitionWithIndexedParameters(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableVariadic::class,
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

    public function testVariadicUsingDefinitionWithIndexedParametersAndCompundTypeArguments(): void
    {
        $object = new \stdClass();
        $callable = static fn() => 'callable';

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableVariadicCompundType::class,
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

    public function testVariadicUsingDefinitionWithIndexedParametersAndInstanceClassArguments(): void
    {
        $container = $this->createContainer(
            [
                'engine-one' => Stub\EngineMarkOne::class,
                'engine-two' => Stub\EngineMarkTwo::class,
                'instance' => [
                    '__class' => Stub\InvokeableVariadic::class,
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

    public function testVariadicUsingDefinitionWithIndexedParametersAndScalarTypeArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableVariadicScalarType::class,
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

    public function testVariadicUsingDefinitionWithIndexedParametersAndSeveralArguments(): void
    {
        $callable = static fn(): string => 'callable';
        $object = new \stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableVariadicSeveralArguments::class,
                    '__invoke()' => [
                        [
                            1,
                            2,
                            3,
                        ],
                        $callable,
                        $object,
                        'InvokeableVariadicSeveralArguments',
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
                'string' => 'InvokeableVariadicSeveralArguments',
                'variadic' => [
                    'Mark One',
                    'Mark Two',
                ],
            ],
            $instance,
        );
    }

    public function testVariadicUsingDefinitionWithIndexedParametersAndWithoutTypeHintArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableVariadicWithoutTypeHint::class,
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

    public function testVariadicUsingDefinitionWithNamedParameters(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableVariadic::class,
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

    public function testVariadicUsingDefinitionWithNamedParametersAndCompundTypeArguments(): void
    {
        $object = new \stdClass();
        $callable = static fn() => 'callable';

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableVariadicCompundType::class,
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

    public function testVariadicUsingDefinitionWithNamedParametersAndDefaultValueArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableVariadicDefaultValue::class,
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
                'class' => 'InvokeableVariadicDefaultValue',
                'variadic' => [
                    'Mark One',
                    'Mark Two',
                ],
            ],
            $instance,
        );
    }

    public function testVariadicUsingDefinitionWithNamedParametersAndInstanceClassArguments(): void
    {
        $container = $this->createContainer(
            [
                'engine-one' => Stub\EngineMarkOne::class,
                'engine-two' => Stub\EngineMarkTwo::class,
                'instance' => [
                    '__class' => Stub\InvokeableVariadic::class,
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

    public function testVariadicUsingDefinitionWithNamedParametersAndScalarTypeArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableVariadicScalarType::class,
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

    public function testVariadicUsingDefinitionWithNamedParametersAndSeveralArguments(): void
    {
        $callable = static fn(): string => 'callable';
        $object = new \stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableVariadicSeveralArguments::class,
                    '__invoke()' => [
                        'array' => [
                            1,
                            2,
                            3,
                        ],
                        'callable' => $callable,
                        'object' => $object,
                        'string' => 'InvokeableVariadicSeveralArguments',
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
                'string' => 'InvokeableVariadicSeveralArguments',
                'variadic' => [
                    'Mark One',
                    'Mark Two',
                ],
            ],
            $instance,
        );
    }

    public function testVariadicUsingDefinitionWithNamedParametersAndSeveralArgumentsDisordered(): void
    {
        $callable = static fn(): string => 'callable';
        $object = new \stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableVariadicSeveralArguments::class,
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
                        'string' => 'InvokeableVariadicSeveralArguments',
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
                'string' => 'InvokeableVariadicSeveralArguments',
                'variadic' => [
                    'Mark One',
                    'Mark Two',
                ],
            ],
            $instance,
        );
    }

    public function testVariadicUsingDefinitionWithNamedParametersAndWithoutTypeHintArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InvokeableVariadicWithoutTypeHint::class,
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

    private function createContainer($definitions = [], $singletons = []): Container
    {
        return new Container($definitions, $singletons);
    }
}
