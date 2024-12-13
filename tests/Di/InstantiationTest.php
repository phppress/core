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

    public function testDefinitionUsingIndexedParametersMethodsWithServeralArguments(): void
    {
        $callable = static function (): int {
            return 42;
        };
        $object = new Stub\Instance();
        $objectCommon = new \stdClass();

        $container = $this->createContainer(
            [
                Stub\InstanceMethodSeveralArguments::class => [
                    'compoundTypes()' => [[1, 2, 3], $callable, [4, 5, 6], $object],
                    'commonArguments()' => [[7, 8, 9], $objectCommon],
                    'scalarTypes()' => [true, 3.14, 42, 'scalar'],
                ],
            ],
        );

        $instance = $container->get(Stub\InstanceMethodSeveralArguments::class);

        $this->assertSame(
            [
                'compoundTypes' => [
                    'array' => [1, 2, 3],
                    'callable' => $callable,
                    'iterable' => [4, 5, 6],
                    'object' => $object,
                ],
                'scalarTypes' => [
                    'boolean' => true,
                    'float' => 3.14,
                    'integer' => 42,
                    'string' => 'scalar',
                ],
            ],
            $instance->getArguments(),
        );
        $this->assertSame(
            [
                'common' => [
                    'array' => [7, 8, 9],
                    'object' => $objectCommon,
                ],
            ],
            $instance->getCommonArguments(),
        );
    }

    public function testDefinitionUsingNamedParametersMethodsWithServeralArguments(): void
    {
        $callable = static function (): int {
            return 42;
        };
        $object = new Stub\Instance();
        $objectCommon = new \stdClass();

        $container = $this->createContainer(
            [
                Stub\InstanceMethodSeveralArguments::class => [
                    'compoundTypes()' => [
                        'array' => [1, 2, 3],
                        'callable' => $callable,
                        'iterable' => [4, 5, 6],
                        'object' => $object,
                    ],
                    'commonArguments()' => [
                        'array' => [7, 8, 9],
                        'object' => $objectCommon,
                    ],
                    'scalarTypes()' => [
                        'boolean' => true,
                        'float' => 3.14,
                        'integer' => 42,
                        'string' => 'scalar',
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\InstanceMethodSeveralArguments::class);

        $this->assertSame(
            [
                'compoundTypes' => [
                    'array' => [1, 2, 3],
                    'callable' => $callable,
                    'iterable' => [4, 5, 6],
                    'object' => $object,
                ],
                'scalarTypes' => [
                    'boolean' => true,
                    'float' => 3.14,
                    'integer' => 42,
                    'string' => 'scalar',
                ],
            ],
            $instance->getArguments(),
        );
        $this->assertSame(
            [
                'common' => [
                    'array' => [7, 8, 9],
                    'object' => $objectCommon,
                ],
            ],
            $instance->getCommonArguments(),
        );
    }

    public function testDefinitionUsingNamedParametersMethodsWithServeralArgumentsDisordered(): void
    {
        $callable = static function (): int {
            return 42;
        };
        $object = new Stub\Instance();
        $objectCommon = new \stdClass();

        $container = $this->createContainer(
            [
                Stub\InstanceMethodSeveralArguments::class => [
                    'compoundTypes()' => [
                        'callable' => $callable,
                        'object' => $object,
                        'array' => [1, 2, 3],
                        'iterable' => [4, 5, 6],
                    ],
                    'commonArguments()' => [
                        'object' => $objectCommon,
                        'array' => [7, 8, 9],
                    ],
                    'scalarTypes()' => [
                        'string' => 'scalar',
                        'integer' => 42,
                        'boolean' => true,
                        'float' => 3.14,
                    ],
                ],
            ],
        );

        $instance = $container->get(Stub\InstanceMethodSeveralArguments::class);

        $this->assertSame(
            [
                'compoundTypes' => [
                    'array' => [1, 2, 3],
                    'callable' => $callable,
                    'iterable' => [4, 5, 6],
                    'object' => $object,
                ],
                'scalarTypes' => [
                    'boolean' => true,
                    'float' => 3.14,
                    'integer' => 42,
                    'string' => 'scalar',
                ],
            ],
            $instance->getArguments(),
        );
        $this->assertSame(
            [
                'common' => [
                    'array' => [7, 8, 9],
                    'object' => $objectCommon,
                ],
            ],
            $instance->getCommonArguments(),
        );
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

    public function testFailsForDefinitionUsingInstanceClassWithMethodPrivate(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InstanceMethodPrivate::class,
                    'privateMethod()' => ['invalid'],
                ]
            ],
        );

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "Method "privateMethod" in class "PHPPress\Tests\Di\Stub\InstanceMethodPrivate" is not publicly accessible."'
        );

        $container->get('instance');
    }

    public function testFailsForDefinitionUsingInstanceClassWithMethodNotFound(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InstanceMethodPrivate::class,
                    'noExist()' => ['invalid'],
                ]
            ],
        );

        $this->expectException(InvalidDefinition::class);
        $this->expectExceptionMessage(
            'Invalid definition: "Method "noExist" not found in class "PHPPress\Tests\Di\Stub\InstanceMethodPrivate"."'
        );

        $container->get('instance');
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

    public function testInstanceReferenceClassArguments(): void
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

    public function testMagicMethod(): void
    {
        $container = $this->createContainer(
            [
                Stub\InstanceMagicMethods::class => [
                    '__clone()' => [],
                    '__debugInfo()' => [],
                    '__destruct()' => [],
                    '__get()' => ['name'],
                    '__isset()' => ['name'],
                    '__set()' => ['name', 'Magic Method'],
                    '__toString()' => [],
                    '__wakeup()' => [],
                ],
            ],
        );

        $instance = $container->get(Stub\InstanceMagicMethods::class);

        $this->assertInstanceOf(Stub\InstanceMagicMethods::class, $instance);
        $this->assertSame('default', $instance->name);

        $instance->name = 'Magic Method';

        $this->assertSame('Magic Method', $instance->name);
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

    public function testVariadicMethodDefinitionUsingIndexedParameters(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InstanceMethodVariadic::class,
                    'variadic()' => [[new Stub\EngineMarkOne(), new Stub\EngineMarkTwo()]],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\InstanceMethodVariadic::class, $instance);
        $this->assertSame(['variadic' => ['Mark One', 'Mark Two']], $instance->getVariadic());
    }

    public function testVariadicMethodDefinitionUsingIndexedParametersAndCompundTypesAndSeveralArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InstanceMethodVariadic::class,
                    'variadicCompundTypes()' => [[1, 2, 3], [new Stub\EngineMarkTwo(), null]],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\InstanceMethodVariadic::class, $instance);
        $this->assertSame(
            [
                'compundTypes' => [
                    'array' => [1, 2, 3],
                    'variadic' => ['red', null]
                ],
            ],
            $instance->getVariadicCompundTypes(),
        );
    }

    public function testVariadicMethodDefinitionUsingIndexedParametersAndScalarTypesAndSeveralArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InstanceMethodVariadic::class,
                    'variadicScalarTypes()' => [[1, 2, 3], [true, 1, 3.14, 'scalar']],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\InstanceMethodVariadic::class, $instance);
        $this->assertSame(
            [
                'scalarTypes' => [
                    'array' => [1, 2, 3],
                    'variadic' => [true, 1, 3.14, 'scalar'],
                ],
            ],
            $instance->getVariadicScalarTypes(),
        );
    }

    public function testVariadicMethodDefinitionUsingNamedParameters(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InstanceMethodVariadic::class,
                    'variadic()' => ['variadic' => [new Stub\EngineMarkOne(), new Stub\EngineMarkTwo()]],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\InstanceMethodVariadic::class, $instance);
        $this->assertSame(['variadic' => ['Mark One', 'Mark Two']], $instance->getVariadic());
    }

    public function testVariadicMethodDefinitionUsingNamedParametersAndCompundTypesAndSeveralArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InstanceMethodVariadic::class,
                    'variadicCompundTypes()' => [
                        'array' => [1, 2, 3],
                        'variadic' => [new Stub\EngineMarkTwo(), null],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\InstanceMethodVariadic::class, $instance);
        $this->assertSame(
            [
                'compundTypes' => [
                    'array' => [1, 2, 3],
                    'variadic' => ['red', null]
                ],
            ],
            $instance->getVariadicCompundTypes(),
        );
    }

    public function testVariadicMethodDefinitionUsingNamedParametersAndScalarTypesAndSeveralArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InstanceMethodVariadic::class,
                    'variadicScalarTypes()' => [
                        'array' => [1, 2, 3],
                        'variadic' => [true, 1, 3.14, 'scalar'],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\InstanceMethodVariadic::class, $instance);
        $this->assertSame(
            [
                'scalarTypes' => [
                    'array' => [1, 2, 3],
                    'variadic' => [true, 1, 3.14, 'scalar'],
                ],
            ],
            $instance->getVariadicScalarTypes(),
        );
    }

    public function testVariadicMethodDefinitionUsingNamedParametersAndCompundTypesAndSeveralArgumentsDisordered(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InstanceMethodVariadic::class,
                    'variadicCompundTypes()' => [
                        'variadic' => [new Stub\EngineMarkTwo(), null],
                        'array' => [1, 2, 3],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\InstanceMethodVariadic::class, $instance);
        $this->assertSame(
            [
                'compundTypes' => [
                    'array' => [1, 2, 3],
                    'variadic' => ['red', null]
                ],
            ],
            $instance->getVariadicCompundTypes(),
        );
    }

    public function testVariadicMethodDefinitionUsingNamedParametersAndOptionalSeveralArguments(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InstanceMethodVariadic::class,
                    'variadicOptional()' => [
                        'variadic' => [new Stub\EngineMarkOne(), new Stub\EngineMarkTwo()],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\InstanceMethodVariadic::class, $instance);
        $this->assertSame(
            [
                'optional' => [
                    'array' => [1, 2, 3],
                    'variadic' => ['Mark One', 'Mark Two'],
                ],
            ],
            $instance->getVariadicOptional(),
        );
    }

    public function testVariadicMethodDefinitionUsingNamedParametersAndScalarTypesAndSeveralArgumentsDisordered(): void
    {
        $container = $this->createContainer(
            [
                'instance' => [
                    '__class' => Stub\InstanceMethodVariadic::class,
                    'variadicScalarTypes()' => [
                        'variadic' => [true, 1, 3.14, 'scalar'],
                        'array' => [1, 2, 3],
                    ],
                ],
            ],
        );

        $instance = $container->get('instance');

        $this->assertInstanceOf(Stub\InstanceMethodVariadic::class, $instance);
        $this->assertSame(
            [
                'scalarTypes' => [
                    'array' => [1, 2, 3],
                    'variadic' => [true, 1, 3.14, 'scalar'],
                ],
            ],
            $instance->getVariadicScalarTypes(),
        );
    }

    private function createContainer($definitions = [], $singletons = []): Container
    {
        return new Container($definitions, $singletons);
    }
}
