<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di;

use ArgumentCountError;
use PHPPress\Di\Exception\NotInstantiable;
use PHPPress\Di\{Container, Instance};
use PHPPress\Exception\InvalidConfig;
use PHPPress\Tests\Di\Stub\EngineInterface;
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
    private Container $container;

    public function setUp(): void
    {
        $this->container = new Container();

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->container);

        parent::tearDown();
    }

    public function testCreate(): void
    {
        $definitionInstance = $this->container->create(Stub\DefinitionClass::class);

        $this->assertInstanceOf(Stub\DefinitionClass::class, $definitionInstance);
        $this->assertSame(0, $definitionInstance->getA());
        $this->assertSame(0, $definitionInstance->getB());
    }

    public function testCreateWithClassAliaseName(): void
    {
        $this->container->set('definition', ['__class' => Stub\DefinitionClass::class]);

        $definitionInstance = $this->container->create('definition');

        $this->assertInstanceOf(Stub\DefinitionClass::class, $definitionInstance);
        $this->assertSame(0, $definitionInstance->getA());
        $this->assertSame(0, $definitionInstance->getB());
    }

    public function testCreateWithClassInterface(): void
    {
        $this->container->set(Stub\DefinitionClassInterface::class, Stub\DefinitionClass::class);

        $definitionInstance = $this->container->create(Stub\DefinitionClassInterface::class);

        $this->assertInstanceOf(Stub\DefinitionClass::class, $definitionInstance);
        $this->assertSame(0, $definitionInstance->getA());
        $this->assertSame(0, $definitionInstance->getB());
    }

    public function testCreateWithConstructorArgumentIsMissing(): void
    {
        $this->expectException(ArgumentCountError::class);
        $this->expectExceptionMessage(
            'Too few arguments to function PHPPress\Tests\Di\Stub\ConstructorUnionType::__construct(), 0 ' .
            'passed and exactly 1 expected',
        );

        new Container()->create(Stub\ConstructorUnionType::class);
    }

    public function testCreateWithConstructorUnionTypeArguments(): void
    {
        $unionTypeInstance = new Container()->create(
            Stub\ConstructorUnionType::class,
            ['__construct()' => ['value' => 'a']],
        );

        $this->assertInstanceOf(Stub\ConstructorUnionType::class, $unionTypeInstance);

        $unionTypeInstance = new Container()->create(Stub\ConstructorUnionType::class, ['__construct()' => [1]]);

        $this->assertInstanceOf(Stub\ConstructorUnionType::class, $unionTypeInstance);

        $unionTypeInstance = new Container()->create(Stub\ConstructorUnionType::class, ['__construct()' => [2.3]]);

        $this->assertInstanceOf(Stub\ConstructorUnionType::class, $unionTypeInstance);

        $unionTypeInstance = new Container()->create(
            Stub\ConstructorUnionType::class,
            ['__construct()' => ['value' => true]],
        );

        $this->assertInstanceOf(Stub\ConstructorUnionType::class, $unionTypeInstance);
    }

    public function testCreateWithConstructorVaradicArguments(): void
    {
        $varadicInstance = new Container()->create(
            Stub\ConstructorVaradic::class,
            ['__construct()' => [200, 'a', 'b', 'c']],
        );

        $this->assertInstanceOf(Stub\ConstructorVaradic::class, $varadicInstance);
        $this->assertsame(200, $varadicInstance->getKey());
        $this->assertSame(['a', 'b', 'c'], $varadicInstance->getValue());
    }

    public function testCreateWithConstructorVaradicMixedArguments(): void
    {
        $varadicInstance = new Container()->create(
            Stub\ConstructorVaradicMixed::class,
            ['__construct()' => [1, 'a', 2.3]],
        );

        $this->assertInstanceOf(Stub\ConstructorVaradicMixed::class, $varadicInstance);
        $this->assertSame([1, 'a', 2.3], $varadicInstance->getValue());
    }

    public function testCreateWithConstructorVaradicNotTypeHintArguments(): void
    {
        $varadicInstance = new Container()->create(
            Stub\ConstructorVaradicNotTypeHint::class,
            ['__construct()' => [1, 'a', 2.3]],
        );

        $this->assertInstanceOf(Stub\ConstructorVaradicNotTypeHint::class, $varadicInstance);
        $this->assertSame([1, 'a', 2.3], $varadicInstance->getValue());
    }

    public function testCreateWithConstructorVaradicUnionTypeArguments(): void
    {
        $varadicInstance = new Container()->create(
            Stub\ConstructorUnionTypeVaradic::class,
            ['__construct()' => [true, 1, 'a']],
        );

        $this->assertInstanceOf(Stub\ConstructorUnionTypeVaradic::class, $varadicInstance);
        $this->assertSame([true, 1, 'a'], $varadicInstance->getValue());
    }

    public function testCreateWithDefinitions(): void
    {
        $definitionInstance = $this->container->create(
            Stub\DefinitionClass::class,
            [
                'setA()' => [42],
                'setB()' => [142],
                'c' => 242,
            ],
        );

        $this->assertInstanceOf(Stub\DefinitionClass::class, $definitionInstance);
        $this->assertSame(42, $definitionInstance->getA());
        $this->assertSame(142, $definitionInstance->getB());
        $this->assertSame(242, $definitionInstance->c);
    }

    public function testCreateWitkHook(): void
    {
        $this->container->set(
            'hook',
            [
                '__class' => Stub\Hook::class,
                'firstName' => 'john',
                'lastName' => 'doe',
            ],
        );

        $hookInstance = $this->container->create('hook');

        $this->assertInstanceOf(Stub\Hook::class, $hookInstance);
        $this->assertSame('John Doe', $hookInstance->fullName);
    }

    public function testCreateWithSetterInmutable(): void
    {
        $definitionInstance = $this->container->create(Stub\DefinitionClass::class, ['withD()' => [1000]]);

        $this->assertInstanceOf(Stub\DefinitionClass::class, $definitionInstance);
        $this->assertSame(1000, $definitionInstance->d);
    }

    public function testCreateWithNotInstantiable(): void
    {
        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage(
            'Not instantiable exception: "Failed to instantiate component or class: "NonExistentClass"."',
        );

        $this->container->create(\NonExistentClass::class);
    }

    public function testGet(): void
    {
        $fooClass = Stub\Foo::class;
        $barClass = Stub\Bar::class;
        $definitionInstance = Stub\DefinitionClass::class;

        // automatic wiring
        $this->container->set(Stub\DefinitionClassInterface::class, $definitionInstance);

        $fooInstance = $this->container->get($fooClass);

        $this->assertInstanceOf($fooClass, $fooInstance);
        $this->assertInstanceOf($barClass, $fooInstance->bar);
        $this->assertInstanceOf($definitionInstance, $fooInstance->bar->definitionInstance);

        $fooInstance2 = $this->container->get($fooClass);

        $this->assertNotSame($fooInstance, $fooInstance2);

        // full wiring
        $this->container->clear();

        $this->container->set(Stub\DefinitionClassInterface::class, $definitionInstance);
        $this->container->set($barClass);
        $this->container->set($fooClass);

        $fooInstance = $this->container->get($fooClass);

        $this->assertInstanceOf($fooClass, $fooInstance);
        $this->assertInstanceOf($barClass, $fooInstance->bar);
        $this->assertInstanceOf($definitionInstance, $fooInstance->bar->definitionInstance);

        // wiring by closure
        $this->container->clear();

        $this->container->set(
            'foo',
            static function (): Stub\Foo {
                $definitionInstance = new Stub\DefinitionClass();
                $barInstance = new Stub\Bar($definitionInstance);

                return new Stub\Foo($barInstance);
            },
        );

        $fooInstance = $this->container->get('foo');

        $this->assertInstanceOf($fooClass, $fooInstance);
        $this->assertInstanceOf($barClass, $fooInstance->bar);
        $this->assertInstanceOf($definitionInstance, $fooInstance->bar->definitionInstance);

        // wiring by closure which uses container
        $this->container->clear();

        $this->container->set(Stub\DefinitionClassInterface::class, $definitionInstance);
        $this->container->set(
            'foo',
            static function (Container $c, array $config = []): Stub\Foo {
                return $c->get(Stub\Foo::class);
            },
        );

        $fooInstance = $this->container->get('foo');

        $this->assertInstanceOf($fooClass, $fooInstance);
        $this->assertInstanceOf($barClass, $fooInstance->bar);
        $this->assertInstanceOf($definitionInstance, $fooInstance->bar->definitionInstance);

        // predefined constructor parameters
        $this->container->clear();

        $this->container->set('foo', ['__class' => $fooClass, '__construct()' => [Instance::of('bar')]]);
        $this->container->set('bar', ['__class' => $barClass, '__construct()' => [Instance::of('definitionInstance')]]);
        $this->container->set('definitionInstance', $definitionInstance);

        $fooInstance = $this->container->get('foo');

        $this->assertInstanceOf($fooClass, $fooInstance);
        $this->assertInstanceOf($barClass, $fooInstance->bar);
        $this->assertInstanceOf($definitionInstance, $fooInstance->bar->definitionInstance);

        // predefined property parameters
        $this->container->clear();

        $fooPropertyClass = Stub\FooProperty::class;
        $barSetterClass = Stub\BarSetter::class;

        $this->container->set(
            'foo',
            [
                '__class' => $fooPropertyClass,
                'bar' => Instance::of('bar'),
            ],
        );
        $this->container->set(
            'bar',
            [
                '__class' => $barSetterClass,
                'setDefinitionClass()' => [Instance::of('definitionInstance')],
            ],
        );
        $this->container->set('definitionInstance', $definitionInstance);

        $fooInstance = $this->container->get('foo');

        $this->assertInstanceOf($fooPropertyClass, $fooInstance);
        $this->assertInstanceOf($barSetterClass, $fooInstance->bar);
        $this->assertInstanceOf($definitionInstance, $fooInstance->bar->getDefinitionClass());

        // setter inmutable
        $this->container->clear();

        $this->container->set('definitionInstance', ['__class' => Stub\DefinitionClass::class, 'withD()' => [1000]]);

        $definitionInstance = $this->container->get('definitionInstance');

        $this->assertSame(1000, $definitionInstance->d);

        // wiring by closure
        $this->container->clear();

        $this->container->set('definitionInstance', new Stub\DefinitionClass());

        $definitionInstanceInstance1 = $this->container->get('definitionInstance');
        $definitionInstanceInstance2 = $this->container->get('definitionInstance');

        $this->assertSame($definitionInstanceInstance1, $definitionInstanceInstance2);
    }

    public function testGetWithCallable(): void
    {
        $this->container->setDefinitions(
            [
                'test' => static function (): int {
                    return 42;
                },
            ],
        );

        $this->assertSame(42, $this->container->get('test'));
    }

    public function testGetWithClassIndirectly(): void
    {
        $this->container->setDefinitions(
            [
                'definition' => Stub\DefinitionClass::class,
                Stub\DefinitionClass::class => [
                    'setA()' => [42],
                ],
            ],
        );

        $definitionInstance = $this->container->get('definition');

        $this->assertInstanceOf(Stub\definitionClass::class, $definitionInstance);
        $this->assertSame(42, $definitionInstance->getA());
    }

    public function testGetWithConstructorNullableArguments(): void
    {
        $this->container->set(Stub\ConstructorNullableArguments::class, ['__construct()' => [null]]);

        $this->assertInstanceOf(
            Stub\ConstructorNullableArguments::class,
            $this->container->get(Stub\ConstructorNullableArguments::class),
        );
    }

    public function testGetWithConstructorNullableAndNamedArguments(): void
    {
        $this->container->set(Stub\ConstructorNullableArguments::class, ['__construct()' => ['car' => null]]);

        $this->assertInstanceOf(
            Stub\ConstructorNullableArguments::class,
            $this->container->get(Stub\ConstructorNullableArguments::class),
        );
    }

    public function testGetWithConstructorNullableValueArgumentDefault(): void
    {
        $this->assertTrue($this->container->has(Stub\ConstructorNullValueArgumentDefault::class));

        $service = $this->container->get(Stub\ConstructorNullValueArgumentDefault::class);

        $this->assertNull($service->getCar());
    }

    public function testGetWithConstructorNullableValueArgumentDefaultAndDefinition(): void
    {
        $this->container->setDefinitions(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
            ],
        );

        $this->assertTrue($this->container->has(Stub\ConstructorNullValueArgumentDefault::class));

        $service = $this->container->get(Stub\ConstructorNullValueArgumentDefault::class);

        $this->assertInstanceOf(Stub\Car::class, $service->getCar());
    }

    public function testGetWithInvalidConfigForNonAssociativeOrListConstructorArguments(): void
    {
        $this->container->set(Stub\ConstructorNullableArguments::class, ['__construct()' => ['car' => null, 1]]);

        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage(
            'Invalid configuration: "Dependencies indexed by name and by position in the same array are not allowed."',
        );

        $this->container->get(Stub\ConstructorNullableArguments::class);
    }

    #[DataProviderExternal(ContainerProvider::class, 'notfountException')]
    public function testGetWithNotFound(string $class, string $expectedExceptionMessage): void
    {
        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->container->get($class);
    }

    public function testGetWithNotInstantiable(): void
    {
        $this->container->setDefinitions(
            [
                'test' => [
                    'class' => '42',
                ],
            ],
        );

        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage('Not instantiable exception: "Failed to instantiate component or class: "42".""');

        $this->container->get('test');
    }

    public function testGetWithNotInstantiableWhenConstructorArgumentIsMissing(): void
    {
        $className = Stub\ConstructorNullableArguments::class;

        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage(
            'Not instantiable exception: "Missing required parameter "car" when instantiating "' . $className . '"."',
        );

        $this->container->get($className);
    }

    public function testGetVariadicConstructor(): void
    {
        $this->assertInstanceOf(Stub\EngineStorage::class, $this->container->get(Stub\EngineStorage::class));
    }

    public function testGetDefinitions(): void
    {
        $this->container->setDefinitions(
            [
                'definitionInstance' => [
                    '__class' => Stub\DefinitionClass::class,
                    'a()' => 42,
                ],
            ],
        );

        $this->assertSame(
            [
                'definitionInstance' => [
                    'a()' => 42,
                    'class' => Stub\DefinitionClass::class,
                ],
            ],
            $this->container->getDefinitions(),
        );
    }

    #[DataProviderExternal(ContainerProvider::class, 'has')]
    public function testHas(bool $expected, $id): void
    {
        $this->container->setDefinitions(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
            ],
        );

        $this->assertSame($expected, $this->container->has($id));
    }

    public function testHasSingleton(): void
    {
        $this->container->setSingletons([Stub\DefinitionClass::class => Stub\DefinitionClass::class]);

        $this->assertTrue($this->container->hasSingleton(Stub\DefinitionClass::class));
    }

    public function testHasSingletonWithClassAliases(): void
    {
        $this->container->setSingletons(['definitionInstance' => Stub\DefinitionClass::class]);

        $this->assertTrue($this->container->hasSingleton('definitionInstance'));
    }

    public function testHasSingletonWithDefinitionInstance(): void
    {
        $this->container->setSingletons(['definitionInstanceInstance' => Instance::of(Stub\DefinitionClass::class)]);

        $this->assertTrue($this->container->hasSingleton('definitionInstanceInstance'));
    }

    public function testHasSingletonWithCheckInstance(): void
    {
        $this->container->setSingletons(['definitionInstance' => Stub\DefinitionClass::class]);

        $this->assertInstanceOf(Stub\DefinitionClass::class, $this->container->get('definitionInstance'));
        $this->assertTrue($this->container->hasSingleton('definitionInstance', true));
    }

    public function testInvokeWithConcreteDependenciesClass(): void
    {
        $closure = static fn(
            Stub\ConstructorNullValueArgumentDefault $optionalConcreteDependency,
        ): bool => $optionalConcreteDependency->getCar() !== null;

        $this->assertTrue(
            $this->container->invoke(
                $closure,
                [new Stub\ConstructorNullValueArgumentDefault(new Stub\Car(new Stub\EngineMarkOne()))],
            ),
        );
    }

    public function testInvokeWithConcreteDependenciesClassException(): void
    {
        $closure = static fn(Stub\Bar $bar): Stub\Bar => $bar;

        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage(
            'Not instantiable exception: "Missing required parameter "definitionInstance" when instantiating ' .
            '"PHPPress\Tests\Di\Stub\Bar"."',
        );

        $this->container->invoke($closure);
    }

    public function testInvokeWithOptionalDependenciesClass(): void
    {
        $closure = static fn(
            Stub\DefinitionClass|null $definitionInstance = null,
        ): bool => $definitionInstance !== null;

        $this->assertFalse($this->container->invoke($closure));
        $this->assertFalse($this->container->invoke($closure, [null]));
        $this->assertTrue($this->container->invoke($closure, [new Stub\DefinitionClass()]));
        $this->assertFalse($this->container->invoke($closure, ['definitionInstance' => null]));
        $this->assertTrue($this->container->invoke($closure, ['definitionInstance' => new Stub\DefinitionClass()]));
    }

    public function testInvokeWithVariadicCallable(): void
    {
        $this->container->set(
            'oneDefinitionClass',
            [
                '__class' => Stub\DefinitionClass::class,
                'setA()' => [100],
            ],
        );
        $this->container->set(
            'twoDefinitionClass',
            [
                '__class' => Stub\DefinitionClass::class,
                'setA()' => [200],
            ],
        );
        $callable = static function (Stub\DefinitionClass ...$definitionInstance): string {
            return
                'The property "a" is: [' . $definitionInstance[0]->getA() . ', ' . $definitionInstance[1]->getA() . ']';
        };

        $params = [$this->container->get('oneDefinitionClass'), $this->container->get('twoDefinitionClass')];

        $this->assertSame('The property "a" is: [100, 200]', $this->container->invoke($callable, $params));
    }

    public function testResolveCallableDependenciesWithInvokeableClass(): void
    {
        $closure = new Stub\Invokeable();

        $resolvedDependencies = $this->container->resolveCallableDependencies($closure);
        $result = $closure(...$resolvedDependencies);

        $this->assertSame('invoked', $result);
    }

    public function testResolveCallableDependencies(): void
    {
        $closure = static fn(int $a, int $b): bool => $a > $b;

        $this->assertSame([1, 5], $this->container->resolveCallableDependencies($closure, ['b' => 5, 'a' => 1]));
        $this->assertSame([1, 5], $this->container->resolveCallableDependencies($closure, ['a' => 1, 'b' => 5]));
        $this->assertSame([1, 5], $this->container->resolveCallableDependencies($closure, [1, 5]));
    }

    public function testResolveCallableDependenciesWithIntersectionTypes(): void
    {
        $this->container->set(Stub\DefinitionClassInterface::class, Stub\DefinitionClass::class);

        $params = $this->container->resolveCallableDependencies(
            [
                Stub\StaticIntersectionType::class,
                'anotherDefinitionClassAndDefinitionClassInterfaceIntersection',
            ],
        );

        $this->assertInstanceOf(Stub\AnotherDefinitionClass::class, $params[0]);

        $params = $this->container->resolveCallableDependencies(
            [
                Stub\StaticIntersectionType::class,
                'definitionClassInterfaceAndDefinitionClassIntersection',
            ],
        );

        $this->assertInstanceOf(Stub\DefinitionClass::class, $params[0]);
    }

    public function testResolveCallableDependencieWithUnionTypesAndStaticMethod(): void
    {
        $this->container->set(Stub\EngineInterface::class, Stub\EngineMarkOne::class);

        $params = $this->container->resolveCallableDependencies([Stub\StaticUnionType::class, 'engineCar']);

        $this->assertInstanceOf(Stub\EngineMarkOne::class, $params[0]);
    }

    public function testSetWithNulledConstructorParameters(): void
    {
        $alpha = $this->container->get(Stub\Alpha::class);

        $this->assertInstanceOf(Stub\Beta::class, $alpha->beta);
        $this->assertNull($alpha->omega);

        $this->container->clear();
        $this->container->set(Stub\DefinitionClassInterface::class, Stub\DefinitionClass::class);

        $alpha = $this->container->get(Stub\Alpha::class);

        $this->assertInstanceOf(Stub\Beta::class, $alpha->beta);
        $this->assertInstanceOf(Stub\DefinitionClass::class, $alpha->omega);
        $this->assertNull($alpha->unknown);
        $this->assertNull($alpha->color);

        $this->container->clear();
        $this->container->set(Stub\AbstractColor::class, Stub\Color::class);

        $alpha = $this->container->get(Stub\Alpha::class);

        $this->assertInstanceOf(Stub\Color::class, $alpha->color);
    }

    public function testSetWithInvalidDefinition(): void
    {
        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage('Invalid configuration: "Invalid definition for "test": invalid"');

        $this->container->set('test', 'invalid');
    }

    public function testSetDefinitions(): void
    {
        $this->container->setDefinitions(
            [
                Stub\EngineInterface::class => Stub\EngineMarkOne::class,
            ],
        );

        $this->assertInstanceOf(Stub\EngineMarkOne::class, $this->container->get(Stub\EngineInterface::class));
    }

    public function testSetDefinitionsWithInvalidDefinitionArray(): void
    {
        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage(
            'Invalid configuration: "A class definition requires a "__class" or "class" member."',
        );

        $this->container->setDefinitions(
            [
                'test' => [
                    'a' => 42,
                ],
            ],
        );
    }

    public function testSetDefinitionsWithInvalidDefinitionInteger(): void
    {
        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage(
            'Invalid configuration: "A class definition requires a "__class" or "class" member."',
        );

        $this->container->setDefinitions(
            [
                'test' => [
                    42,
                ],
            ],
        );
    }

    public function testSetDefinitionsWithInvalidDefinitionString(): void
    {
        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage(
            'Invalid configuration: "A class definition requires a "__class" or "class" member."',
        );

        $this->container->setDefinitions(
            [
                'test' => [
                    '42',
                ],
            ],
        );
    }

    public function testSetDefinitionsWithIntegerKeys(): void
    {
        $this->container->setDefinitions(
            [
                Stub\EngineMarkOne::class => Stub\EngineMarkOne::class,
                EngineInterface::class => Stub\EngineMarkOne::class,
            ],
        );

        $carInstance = $this->container->get(Stub\Car::class);

        $this->assertInstanceOf(Stub\EngineMarkOne::class, $carInstance->getEngine());
        $this->assertNotSame($carInstance->getEngine(), $this->container->get(Stub\EngineMarkOne::class));
    }

    public function testSetDefinitionsWithIntegerKeysException(): void
    {
        $className = Stub\Car::class;
        $this->container->setDefinitions(
            [
                Stub\EngineMarkOne::class,
                Stub\EngineMarkTwo::class,
            ],
        );

        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage(
            'Not instantiable exception: "Missing required parameter "engine" when instantiating "' .
            $className . '"."',
        );

        $this->container->get($className);
    }

    public function testSetDefinitionsWithObjectClass(): void
    {
        $this->container->setDefinitions(['definitionInstance' => new Stub\DefinitionClass()]);

        $definitionInstance = $this->container->get('definitionInstance');

        $this->assertInstanceOf(Stub\DefinitionClass::class, $definitionInstance);
        $this->assertSame(0, $definitionInstance->getA());
    }

    public function testSetDefinitionWithScalar(): void
    {
        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage('Invalid configuration: "Unsupported definition type for "integer"."');

        $this->container->setDefinitions(['scalar' => 42]);
    }

    public function testSetDefinitionWithStaticCall(): void
    {
        $this->container->setDefinitions(
            [
                'definitionInstance' => [Stub\DefinitionStaticClassFactory::class, 'create'],
            ],
        );

        $objectClass = $this->container->get('definitionInstance');

        $this->assertInstanceOf(Stub\DefinitionClass::class, $objectClass);
        $this->assertSame(42, $objectClass->getA());
        $this->assertSame(0, $objectClass->getB());
    }

    public function testSetSingletonsWithIntegerKeys(): void
    {
        $this->container->SetSingletons(
            [
                Stub\EngineMarkOne::class => Stub\EngineMarkOne::class,
                EngineInterface::class => Stub\EngineMarkOne::class,
            ],
        );

        $carInstance = $this->container->get(Stub\Car::class);

        $this->assertInstanceOf(Stub\EngineMarkOne::class, $carInstance->getEngine());
        $this->assertSame($carInstance->getEngine(), $this->container->get(Stub\EngineMarkOne::class));
    }

    public function testSetSingletonsWithIntegerKeysException(): void
    {
        $clasName = Stub\Car::class;
        $this->container->SetSingletons(
            [
                Stub\EngineMarkOne::class,
                Stub\EngineMarkTwo::class,
            ],
        );

        $this->expectException(NotInstantiable::class);
        $this->expectExceptionMessage(
            'Not instantiable exception: "Missing required parameter "engine" when instantiating "' . $clasName . '"."',
        );

        $this->container->get($clasName);
    }

    public function testSetSingletonsWithReferences(): void
    {
        $definitionInstanceInterface = Stub\DefinitionClassInterface::class;

        $this->container->setSingletons(
            [
                $definitionInstanceInterface => [
                    '__class' => Stub\DefinitionClass::class,
                    'setA()' => [42],
                ],
                'definitionInstance' => Instance::of($definitionInstanceInterface),
                'bar' => [
                    '__class' => Stub\Bar::class,
                ],
                'corge' => [
                    '__class' => Stub\Corge::class,
                    '__construct()' => [
                        [
                            'definitionInstance' => Instance::of('definitionInstance'),
                            'bar' => Instance::of('bar'),
                            'definitionInstance33' => new Stub\DefinitionClass(),
                        ],
                    ],
                ],
            ],
        );

        $corge = $this->container->get('corge');

        $this->assertInstanceOf(Stub\Corge::class, $corge);

        $definitionInstance = $corge->map['definitionInstance'];

        $this->assertInstanceOf(Stub\DefinitionClass::class, $definitionInstance);
        $this->assertSame(42, $definitionInstance->getA());

        $bar = $corge->map['bar'];

        $this->assertInstanceOf(Stub\Bar::class, $bar);
        $this->assertSame($definitionInstance, $bar->definitionInstance);

        $definitionInstance33 = $corge->map['definitionInstance33'];
        $this->assertInstanceOf(Stub\DefinitionClass::class, $definitionInstance33);
        $this->assertSame(0, $definitionInstance33->getA());
    }
}
