<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di;

use ArrayIterator;
use DateTime;
use PHPPress\Di\Container;
use PHPPress\Di\Definition\Instance;
use PHPPress\Exception\{InvalidArgument, InvalidDefinition};
use PHPPress\Factory\Exception\{CircularDependency, NotInstantiable};
use PHPPress\Tests\Support\Assert;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test case for the {@see Container} class with constructor arguments.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('di')]
final class ConstructorTest extends \PHPUnit\Framework\TestCase
{
    public function testBuiltInPHPClassUsingInstatiableClass(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\ConstructorBuiltInPHPClass::class);

        $this->assertInstanceOf(Stub\ConstructorBuiltInPHPClass::class, $instance);
        $this->assertInstanceOf(DateTime::class, $instance->getDateTime());
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}/', $instance->getFormattedDate());
    }

    public function testBuiltInPHPClassUsingInstatiableClassAndIndexedParameters(): void
    {
        $dateTime = new DateTime('2024-01-01');
        $container = $this->createContainer(
            [
                Stub\ConstructorBuiltInPHPClass::class => [
                    '__construct()' => [
                        $dateTime,
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\ConstructorBuiltInPHPClass::class);

        $this->assertInstanceOf(Stub\ConstructorBuiltInPHPClass::class, $instance);
        $this->assertInstanceOf(DateTime::class, $instance->getDateTime());
        $this->assertSame('2024-01-01', $instance->getFormattedDate());
    }

    public function testBuiltInPHPClassUsingInstatiableClassAndNamedParameters(): void
    {
        $dateTime = new DateTime('2024-01-01');
        $container = $this->createContainer(
            [
                Stub\ConstructorBuiltInPHPClass::class => [
                    '__construct()' => [
                        'dateTime' => $dateTime,
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\ConstructorBuiltInPHPClass::class);

        $this->assertInstanceOf(Stub\ConstructorBuiltInPHPClass::class, $instance);
        $this->assertInstanceOf(DateTime::class, $instance->getDateTime());
        $this->assertSame('2024-01-01', $instance->getFormattedDate());
    }

    public function testBuiltInPHPClassUsingNotInstatiableClass(): void
    {
        $arrayIterator = new ArrayIterator();

        $container = $this->createContainer(
            [
                Stub\ConstructorBuiltInPHPClassOptional::class => [
                    '__construct()' => [
                        $arrayIterator,
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\ConstructorBuiltInPHPClassOptional::class);

        $this->assertInstanceOf(Stub\ConstructorBuiltInPHPClassOptional::class, $instance);
        $this->assertSame(['iterator' => $arrayIterator], $instance->getConstructorArguments());
    }

    public function testBuiltInPHPClassUsingNotInstatiableClassAndOptionalArguments(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\ConstructorBuiltInPHPClassOptional::class);

        $this->assertInstanceOf(Stub\ConstructorBuiltInPHPClassOptional::class, $instance);
        $this->assertSame(['iterator' => null], $instance->getConstructorArguments());
    }

    public function testCircularDependency(): void
    {
        $container = $this->createContainer(
            [
                Stub\ConstructorCircularA::class => [
                    '__construct()' => [
                        Instance::of(Stub\ConstructorCircularB::class),
                    ],
                ],
                Stub\ConstructorCircularB::class => [
                    '__construct()' => [
                        Instance::of(Stub\ConstructorCircular::class),
                    ],
                ],
                Stub\ConstructorCircular::class => [
                    '__construct()' => [
                        Instance::of(Stub\ConstructorCircularA::class),
                    ],
                ]
            ]
        );

        $this->expectException(CircularDependency::class);
        $this->expectExceptionMessage(
            'Circular dependency detected: PHPPress\Tests\Di\Stub\ConstructorCircular -> PHPPress\Tests\Di\Stub\ConstructorCircularA -> PHPPress\Tests\Di\Stub\ConstructorCircularB -> PHPPress\Tests\Di\Stub\ConstructorCircular."',
        );

        $container->get(Stub\ConstructorCircular::class);
    }

    public function testDefinitionUsingIndexedParameters(): void
    {
        $container = $this->createContainer(
            [
                Stub\Constructor::class => [
                    '__construct()' => [
                        new Stub\Instance(),
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\Constructor::class);

        $this->assertInstanceOf(Stub\Constructor::class, $instance);
    }

    public function testDefinitionUsingIndexedParametersAndCompundTypeArguments(): void
    {
        $callable = static fn() => 'callable';
        $object = new \stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ConstructorCompoundType::class,
                    '__construct()' => [
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

        $this->assertSame(
            ['array' => [1, 2, 3], 'callable' => $callable, 'object' => $object],
            $instance->getConstructorArguments(),
        );
    }

    public function testDefinitionUsingIndexedParametersAndInstanceClassArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance-interface' => Stub\Instance::class,
                'instance' => [
                    '__class' => Stub\Constructor::class,
                    '__construct()' => [
                        Instance::of('instance-interface'),
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\Constructor::class, $instance);
        $this->assertInstanceOf(Stub\Instance::class, $instance->getConstructorArguments());
    }

    public function testDefinitionUsingIndexedParametersAndScalarTypeArguments(): void
    {
        $container = $this->createContainer(
            [
                Stub\ConstructorScalarType::class => [
                    '__construct()' => [
                        true,
                        1,
                        2.3,
                        'scalar',
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\ConstructorScalarType::class);

        $this->assertInstanceOf(Stub\ConstructorScalarType::class, $instance);
        $this->assertSame(
            ['bool' => true, 'int' => 1, 'float' => 2.3, 'string' => 'scalar'],
            $instance->getConstructorArguments(),
        );
    }

    public function testDefinitionUsingNamedParameters(): void
    {
        $container = $this->createContainer(
            [
                Stub\Constructor::class => [
                    '__construct()' => [
                        'instance' => new Stub\Instance(),
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\Constructor::class);

        $this->assertInstanceOf(Stub\Constructor::class, $instance);
    }

    public function testDefinitionUsingNamedParametersAndCompundTypeArguments(): void
    {
        $callable = static fn() => 'callable';
        $object = new \stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ConstructorCompoundType::class,
                    '__construct()' => [
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

        $this->assertSame(
            ['array' => [1, 2, 3], 'callable' => $callable, 'object' => $object],
            $instance->getConstructorArguments(),
        );
    }

    public function testDefinitionUsingNamedParametersAndInstanceClassArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance-interface' => Stub\Instance::class,
                'instance' => [
                    '__class' => Stub\Constructor::class,
                    '__construct()' => [
                        'instance' => Instance::of('instance-interface'),
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\Constructor::class, $instance);
        $this->assertInstanceOf(Stub\Instance::class, $instance->getConstructorArguments());
    }

    public function testDefinitionUsingNamedParametersAndScalarTypeArguments(): void
    {
        $container = $this->createContainer(
            [
                Stub\ConstructorScalarType::class => [
                    '__construct()' => [
                        'bool' => true,
                        'int' => 1,
                        'float' => 2.3,
                        'string' => 'scalar',
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\ConstructorScalarType::class);

        $this->assertInstanceOf(Stub\ConstructorScalarType::class, $instance);
        $this->assertSame(
            ['bool' => true, 'int' => 1, 'float' => 2.3, 'string' => 'scalar'],
            $instance->getConstructorArguments(),
        );
    }

    public function testDependencyStackCleanupOnError(): void
    {
        $container = $this->createContainer();

        $reflectionFactory = Assert::inaccessibleProperty($container, 'reflectionFactory');
        $dependencyStack = Assert::inaccessibleProperty($reflectionFactory, 'dependencyStack');

        $this->assertEmpty($dependencyStack);

        try {
            $container->get('NonExistentClass');
        } catch (NotInstantiable $e) {
            $reflectionFactory = Assert::inaccessibleProperty($container, 'reflectionFactory');
            $dependencyStack = Assert::inaccessibleProperty($reflectionFactory, 'dependencyStack');

            $this->assertEmpty($dependencyStack);
        }

        $instance = $container->get(Stub\ConstructorDefaultValue::class);

        $this->assertInstanceOf(Stub\ConstructorDefaultValue::class, $instance);
    }


    public function testFailsInvalidArguments(): void
    {
        $container = $this->createContainer(
            [
                Stub\Constructor::class => [
                    '__construct()' => [
                        new \stdClass(),
                    ],
                ],
            ],
        );

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage(
            'PHPPress\Tests\Di\Stub\Constructor::__construct(): Argument #1 ($instance) must be of type PHPPress\Tests\Di\Stub\InstanceInterface, stdClass given"',
        );

        $container->get(Stub\Constructor::class);
    }

    public function testFailsInvalidArgumentsFormat(): void
    {
        $callable = static fn() => 'callable';
        $object = new \stdClass();

        $container = $this->createContainer(
            [
                Stub\ConstructorSeveralArguments::class => [
                    '__construct()' => [
                        [
                            1,
                            2,
                            3,
                        ],
                        'callable' => $callable,
                        'object' => $object,
                        'string' => 'FailsInvalidArgumentsFormat',
                    ],
                ],
            ],
        );

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "Dependencies indexed by name and by position in the same array are not allowed."',
        );

        $container->get(Stub\ConstructorSeveralArguments::class);
    }

    public function testFailsMissingArguments(): void
    {
        $container = $this->createContainer();

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "Missing required parameter "instance" when calling "__construct"."',
        );

        $container->get(Stub\Constructor::class);
    }

    public function testMultipleDependencies(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
                Stub\InstanceInterface::class => Stub\Instance::class,
            ],
        );

        $instance = $container->get(Stub\ConstructorMultipleDependencies::class);

        $this->assertInstanceOf(Stub\ConstructorMultipleDependencies::class, $instance);
        $this->assertInstanceOf(Stub\EngineInterface::class, $instance->getFirstDependency());
        $this->assertInstanceOf(Stub\InstanceInterface::class, $instance->getSecondDependency());

        $actions = $instance->performActions();

        $this->assertSame('Mark One', $actions['first']);
        $this->assertSame(0, $actions['second']);
    }

    public function testRetrievesDefaultValueForArguments(): void
    {
        $container = $this->createContainer();

        $instance = $container->get(Stub\ConstructorDefaultValue::class);

        $this->assertInstanceOf(Stub\ConstructorDefaultValue::class, $instance);
        $this->assertSame(
            ['class' => 'ConstructorDefaultValue', 'engine' => null],
            $instance->getConstructorArguments(),
        );
    }

    public function testRetrievesDefaultValueForArgumentsUsingAutoWired(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkTwo::class,
            ],
        );

        $instance = $container->get(Stub\ConstructorDefaultValue::class);

        $this->assertInstanceOf(Stub\ConstructorDefaultValue::class, $instance);
        $this->assertSame(
            ['class' => 'ConstructorDefaultValue', 'engine' => 'Mark Two'],
            $instance->getConstructorArguments(),
        );
    }

    public function testRetrievesDefaultValueForArgumentsUsingIndexedParameters(): void
    {
        $container = $this->createContainer(
            [
                Stub\ConstructorDefaultValue::class => [
                    '__construct()' => [
                        'class' => 'Indexed Parameters',
                        'engine' => new Stub\EngineMarkOne(),
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\ConstructorDefaultValue::class);

        $this->assertInstanceOf(Stub\ConstructorDefaultValue::class, $instance);
        $this->assertSame(
            ['class' => 'Indexed Parameters', 'engine' => 'Mark One'],
            $instance->getConstructorArguments(),
        );
    }

    public function testRetrievesDefaultValueForArgumentsUsingNamedParameters(): void
    {
        $container = $this->createContainer(
            [
                Stub\ConstructorDefaultValue::class => [
                    '__construct()' => [
                        'class' => 'Named Parameters',
                        'engine' => new Stub\EngineMarkOne(),
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\ConstructorDefaultValue::class);

        $this->assertInstanceOf(Stub\ConstructorDefaultValue::class, $instance);
        $this->assertSame(
            ['class' => 'Named Parameters', 'engine' => 'Mark One'],
            $instance->getConstructorArguments(),
        );
    }

    public function testRetrievesDefaultValueForArgumentsUsingNamedParametersDisordered(): void
    {
        $container = $this->createContainer(
            [
                Stub\ConstructorDefaultValue::class => [
                    '__construct()' => [
                        'engine' => new Stub\EngineMarkOne(),
                        'class' => 'Named Parameters',
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\ConstructorDefaultValue::class);

        $this->assertInstanceOf(Stub\ConstructorDefaultValue::class, $instance);
        $this->assertSame(
            ['class' => 'Named Parameters', 'engine' => 'Mark One'],
            $instance->getConstructorArguments(),
        );
    }

    public function testUnionTypeSecondArgumentsUsingAutoWired(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
            ],
        );

        $instance = $container->get(Stub\ConstructorUnionType::class);

        $this->assertInstanceOf(Stub\ConstructorUnionType::class, $instance);
        $this->assertSame(['color' => null, 'engine' => 'Mark One'], $instance->getConstructorArguments());
    }

    public function testUnionTypeFirstArgumentsUsingAutoWired(): void
    {
        $container = $this->createContainer(
            [
                Stub\EngineColorInterface::class => Stub\EngineMarkTwo::class,
            ],
        );

        $instance = $container->get(Stub\ConstructorUnionType::class);

        $this->assertInstanceOf(Stub\ConstructorUnionType::class, $instance);
        $this->assertSame(['color' => 'red', 'engine' => 'Mark Two'], $instance->getConstructorArguments());
    }

    public function testVariadicUsingDefinitionWithIndexedParameters(): void
    {
        $container = $this->createContainer(
            [
                Stub\ConstructorVariadic::class => [
                    '__construct()' => [
                        new Stub\EngineMarkOne(),
                        new Stub\EngineMarkTwo(),
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\ConstructorVariadic::class);

        $this->assertInstanceOf(Stub\ConstructorVariadic::class, $instance);
        $this->assertSame(['variadic' => ['Mark One', 'Mark Two']], $instance->getConstructorArguments());
    }

    public function testVariadicUsingDefinitionWithIndexedParametersAndCompundTypeArguments(): void
    {
        $callable = static fn() => 'callable';
        $object = new \stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ConstructorVariadicCompoundType::class,
                    '__construct()' => [
                        [
                            false,
                            true,
                        ],
                        $callable,
                        $object,
                        null,
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\ConstructorVariadicCompoundType::class, $instance);
        $this->assertSame(
            ['variadic' => [[false, true], $callable, $object, null]],
            $instance->getConstructorArguments(),
        );
    }

    public function testVariadicUsingDefinitionWithIndexedParametersAndInstanceClassArguments(): void
    {
        $container = $this->createContainer(
            [
                'engine-one' => Stub\EngineMarkOne::class,
                'engine-two' => Stub\EngineMarkTwo::class,
                'instance' => [
                    '__class' => Stub\ConstructorVariadic::class,
                    '__construct()' => [
                        Instance::of('engine-one'),
                        Instance::of('engine-two'),
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\ConstructorVariadic::class, $instance);
        $this->assertSame(['variadic' => ['Mark One', 'Mark Two']], $instance->getConstructorArguments());
    }

    public function testVariadicUsingDefinitionWithIndexedParametersAndScalarTypeArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ConstructorVariadicScalarType::class,
                    '__construct()' => [
                        false,
                        100,
                        2.30,
                        'variadic',
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\ConstructorVariadicScalarType::class, $instance);
        $this->assertSame(
            ['variadic' => [false, 100, 2.30, 'variadic']],
            $instance->getConstructorArguments(),
        );
    }

    public function testVariadicUsingDefinitionWithIndexedParametersAndSeveralArguments(): void
    {
        $object = new \stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ConstructorVariadicSeveralArguments::class,
                    '__construct()' => [
                        [
                            1,
                            2,
                            3,
                        ],
                        $object,
                        'InvokableVariadicSeveralArguments',
                        new Stub\EngineMarkOne(),
                        new Stub\EngineMarkTwo(),
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\ConstructorVariadicSeveralArguments::class, $instance);
        $this->assertSame(
            [
                'array' => [
                    1,
                    2,
                    3,
                ],
                'object' => $object,
                'string' => 'InvokableVariadicSeveralArguments',
                'variadic' => [
                    'Mark One',
                    'Mark Two',
                ],
            ],
            $instance->getConstructorArguments(),
        );
    }

    public function testVariadicUsingDefinitionWithIndexedParametersAndWithoutTypeHintArguments(): void
    {
        $container = $this->createContainer(
            [
                Stub\ConstructorVariadicWithoutTypeHint::class => [
                    '__construct()' => [
                        1,
                        'variadic',
                        2.3,
                        true,
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\ConstructorVariadicWithoutTypeHint::class);

        $this->assertInstanceOf(Stub\ConstructorVariadicWithoutTypeHint::class, $instance);
        $this->assertSame(['variadic' => [1, 'variadic', 2.3, true]], $instance->getConstructorArguments());
    }

    public function testVariadicUsingDefinitionWithNamedParameters(): void
    {
        $container = $this->createContainer(
            [
                Stub\ConstructorVariadic::class => [
                    '__construct()' => [
                        'variadic' => [
                            new Stub\EngineMarkOne(),
                            new Stub\EngineMarkTwo(),
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\ConstructorVariadic::class);

        $this->assertSame(['variadic' => ['Mark One', 'Mark Two']], $instance->getConstructorArguments());
    }

    public function testVariadicUsingDefinitionWithNamedParametersAndCompundTypeArguments(): void
    {
        $callable = static fn() => 'callable';
        $object = new \stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ConstructorVariadicCompoundType::class,
                    '__construct()' => [
                        'variadic' => [
                            [
                                false,
                                true,
                            ],
                            $callable,
                            $object,
                            null,
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\ConstructorVariadicCompoundType::class, $instance);
        $this->assertSame(
            ['variadic' => [[false, true], $callable, $object, null]],
            $instance->getConstructorArguments(),
        );
    }

    public function testVariadicUsingDefinitionWithNamedParametersAndInstanceClassArguments(): void
    {
        $container = $this->createContainer(
            [
                'engine-one' => Stub\EngineMarkOne::class,
                'engine-two' => Stub\EngineMarkTwo::class,
                'instance' => [
                    '__class' => Stub\ConstructorVariadic::class,
                    '__construct()' => [
                        'variadic' => [
                            Instance::of('engine-one'),
                            Instance::of('engine-two'),
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\ConstructorVariadic::class, $instance);
        $this->assertSame(['variadic' => ['Mark One', 'Mark Two']], $instance->getConstructorArguments());
    }

    public function testVariadicUsingDefinitionWithNamedParametersAndScalarTypeArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ConstructorVariadicScalarType::class,
                    '__construct()' => [
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

        $this->assertInstanceOf(Stub\ConstructorVariadicScalarType::class, $instance);
        $this->assertSame(
            ['variadic' => [false, 100, 2.30, 'variadic']],
            $instance->getConstructorArguments(),
        );
    }

    public function testVariadicUsingDefinitionWithNamedParametersAndSeveralArguments(): void
    {
        $object = new \stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ConstructorVariadicSeveralArguments::class,
                    '__construct()' => [
                        'array' => [
                            1,
                            2,
                            3,
                        ],
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

        $this->assertInstanceOf(Stub\ConstructorVariadicSeveralArguments::class, $instance);
        $this->assertSame(
            [
                'array' => [
                    1,
                    2,
                    3,
                ],
                'object' => $object,
                'string' => 'InvokableVariadicSeveralArguments',
                'variadic' => [
                    'Mark One',
                    'Mark Two',
                ],
            ],
            $instance->getConstructorArguments(),
        );
    }

    public function testVariadicUsingDefinitionWithNamedParametersAndSeveralArgumentsDisordered(): void
    {
        $object = new \stdClass();

        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\ConstructorVariadicSeveralArguments::class,
                    '__construct()' => [
                        'variadic' => [
                            new Stub\EngineMarkOne(),
                            new Stub\EngineMarkTwo(),
                        ],
                        'object' => $object,
                        'string' => 'InvokableVariadicSeveralArguments',
                        'array' => [
                            1,
                            2,
                            3,
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\ConstructorVariadicSeveralArguments::class, $instance);
        $this->assertSame(
            [
                'array' => [
                    1,
                    2,
                    3,
                ],
                'object' => $object,
                'string' => 'InvokableVariadicSeveralArguments',
                'variadic' => [
                    'Mark One',
                    'Mark Two',
                ],
            ],
            $instance->getConstructorArguments(),
        );
    }

    public function testVariadicUsingDefinitionWithNamedParametersAndWithoutTypeHintArguments(): void
    {
        $container = $this->createContainer(
            [
                Stub\ConstructorVariadicWithoutTypeHint::class => [
                    '__construct()' => [
                        'variadic' => [
                            1,
                            'variadic',
                            2.3,
                            true,
                        ],
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\ConstructorVariadicWithoutTypeHint::class);

        $this->assertInstanceOf(Stub\ConstructorVariadicWithoutTypeHint::class, $instance);
        $this->assertSame(['variadic' => [1, 'variadic', 2.3, true]], $instance->getConstructorArguments());
    }

    private function createContainer($definitions = [], $singletons = []): Container
    {
        return new Container($definitions, $singletons);
    }
}
