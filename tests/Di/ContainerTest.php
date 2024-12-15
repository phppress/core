<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di;

use PHPPress\Di\Container;
use PHPPress\Factory\Instance;
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
                Stub\Instance::class,
            ],
        );

        $this->assertTrue($container->hasSingleton(Stub\Instance::class));
    }

    public function testHasSingletonUsingCheckInstanceWithTrueValue(): void
    {
        $container = $this->createContainer(
            singletons: [
                'instance' => Stub\Instance::class,
            ],
        );

        $this->assertInstanceOf(Stub\Instance::class, $container->get('instance'));
        $this->assertTrue($container->hasSingleton('instance', true));
    }

    public function testHasSingletonUsingCheckInstanceWithTrueValueAndNotInstantiableClass(): void
    {
        $container = $this->createContainer(
            singletons: [
                'instance' => Stub\Instance::class,
            ],
        );

        $this->assertFalse($container->hasSingleton('instance', true));
    }

    public function testHasSingletonUsingClassAliases(): void
    {
        $container = $this->createContainer(
            singletons: [
                'instance' => Stub\Instance::class,
            ],
        );

        $this->assertTrue($container->hasSingleton('instance'));
    }

    public function testHasSingletonUsingClassInstanceReference(): void
    {
        $container = $this->createContainer(
            singletons: [
                'instance' => Instance::of(Stub\Instance::class),
            ],
        );

        $this->assertTrue($container->hasSingleton('instance'));
    }

    public function testRetrieveContainerDefinitions(): void
    {
        $definitions = [
            'instance' => [
                'class' => Stub\Instance::class,
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

        $container->set('instance', Stub\Instance::class);

        $this->assertTrue($container->has('instance'));
        $this->assertInstanceOf(Stub\Instance::class, $container->get('instance'));
    }

    public function testSetSingleton(): void
    {
        $container = $this->createContainer();

        $container->setSingleton('instance', Stub\Instance::class);

        $this->assertTrue($container->hasSingleton('instance'));
        $this->assertInstanceOf(Stub\Instance::class, $container->get('instance'));
    }

    public function testSetSingletonUsingDefinitionWithInstanceClassArguments(): void
    {
        $container = $this->createContainer();

        $container->setSingleton('instance', Instance::of(Stub\Instance::class));

        $instance = $container->get('instance');

        $this->assertTrue($container->hasSingleton('instance'));
        $this->assertInstanceOf(Stub\Instance::class, $instance);
    }

    public function testSetSingletonUsingDefinitionWithObjectArguments(): void
    {
        $container = $this->createContainer();

        $container->setSingleton('instance', new Stub\Instance());

        $instance = $container->get('instance');

        $this->assertTrue($container->hasSingleton('instance'));
        $this->assertInstanceOf(Stub\Instance::class, $instance);
    }

    private function createContainer($definitions = [], $singletons = []): Container
    {
        return new Container($definitions, $singletons);
    }
}
