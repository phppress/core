<?php

declare(strict_types=1);

namespace PHPPress\Tests\Collection;

use Generator;
use PHPPress\Middleware\Collection\ImmutableStack;
use PHPPress\Tests\Middleware\Stub\FirstMiddleware;
use PHPPress\Tests\Middleware\Stub\SecondMiddleware;
use PHPPress\Tests\Middleware\Stub\ThirdMiddleware;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Test case for the {@see \PHPPress\Middleware\Collection\ImmutableStack} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ImmutableStackTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate(): void
    {
        $stack = ImmutableStack::create();

        $this->assertInstanceOf(ImmutableStack::class, $stack);
        $this->assertCount(0, $stack);
    }

    public function testCreateWithItems(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $stack = ImmutableStack::create([$middleware]);

        $this->assertInstanceOf(ImmutableStack::class, $stack);
        $this->assertCount(1, $stack);
    }

    public function testCount(): void
    {
        $stack = ImmutableStack::create(
            [
                new FirstMiddleware(),
                new SecondMiddleware(),
                new ThirdMiddleware(),
            ],
        );

        $this->assertCount(3, $stack);
    }

    public function testFirst(): void
    {
        $middlewares = [
            new FirstMiddleware(),
            new SecondMiddleware(),
            new ThirdMiddleware(),
        ];

        $stack = ImmutableStack::create($middlewares);

        $this->assertSame($middlewares[0], $stack->first());
    }

    public function testFirstWithEmptyStack(): void
    {
        $stack = ImmutableStack::create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot get first item from empty stack.');

        $stack->first();
    }

    public function testGetIterator(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $stack = ImmutableStack::create([$middleware]);

        $this->assertInstanceOf(Generator::class, $stack->getIterator());
        $this->assertCount(1, iterator_to_array($stack->getIterator()));
    }

    public function testIsEmpty(): void
    {
        $stack = ImmutableStack::create();

        $this->assertTrue($stack->isEmpty());
    }

    public function testLast(): void
    {
        $middlewares = [
            new FirstMiddleware(),
            new SecondMiddleware(),
            new ThirdMiddleware(),
        ];

        $stack = ImmutableStack::create($middlewares);

        $this->assertSame($middlewares[2], $stack->last());
    }

    public function testLastWithEmptyStack(): void
    {
        $stack = ImmutableStack::create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot get last item from empty stack.');

        $stack->last();
    }

    public function testMap(): void
    {
        $middlewares = [
            new FirstMiddleware(),
            new SecondMiddleware(),
            new ThirdMiddleware(),
        ];

        $stack = ImmutableStack::create($middlewares);

        $newStack = $stack->map(
            static fn(MiddlewareInterface $middleware): MiddlewareInterface => $middleware,
        );

        $this->assertInstanceOf(ImmutableStack::class, $newStack);
        $this->assertCount(3, $newStack);
    }

    public function testPop(): void
    {
        $middlewares = [
            new FirstMiddleware(),
            new SecondMiddleware(),
            new ThirdMiddleware(),
        ];

        $stack = ImmutableStack::create($middlewares);

        $newStack = $stack->pop();

        $this->assertInstanceOf(ImmutableStack::class, $newStack);
        $this->assertCount(2, $newStack);
    }

    public function testPopWithEmptyStack(): void
    {
        $stack = ImmutableStack::create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot pop from empty stack.');

        $stack->pop();
    }

    public function testPush(): void
    {
        $stack = ImmutableStack::create();

        $this->assertCount(0, $stack);

        $newStack = $stack->push(new FirstMiddleware());

        $this->assertInstanceOf(ImmutableStack::class, $newStack);
        $this->assertCount(1, $newStack);

        $newStack = $newStack->push(new SecondMiddleware());

        $this->assertCount(2, $newStack);
    }

    public function testPushWithItems(): void
    {
        $middleware1 = new FirstMiddleware();
        $middleware2 = new SecondMiddleware();
        $stack = ImmutableStack::create([$middleware1]);

        $newStack = $stack->push($middleware2);

        $this->assertInstanceOf(ImmutableStack::class, $newStack);
        $this->assertCount(2, $newStack);
    }

    public function testShift(): void
    {
        $middlewares = [
            new FirstMiddleware(),
            new SecondMiddleware(),
            new ThirdMiddleware(),
        ];

        $stack = ImmutableStack::create($middlewares);

        $this->assertInstanceOf(FirstMiddleware::class, $stack->first());

        $newStack = $stack->shift();

        $this->assertInstanceOf(ImmutableStack::class, $newStack);
        $this->assertInstanceOf(SecondMiddleware::class, $newStack->first());
        $this->assertInstanceOf(ThirdMiddleware::class, $newStack->last());
        $this->assertCount(2, $newStack);
    }

    public function testShiftWithEmptyStack(): void
    {
        $stack = ImmutableStack::create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot shift from empty stack.');

        $stack->shift();
    }

    public function testShiftWithOneItem(): void
    {
        $middleware = new FirstMiddleware();
        $stack = ImmutableStack::create([$middleware]);

        $newStack = $stack->shift();

        $this->assertInstanceOf(ImmutableStack::class, $newStack);
        $this->assertCount(0, $newStack);
    }

    public function testToArray(): void
    {
        $middleware1 = new FirstMiddleware();
        $middleware2 = new SecondMiddleware();
        $stack = ImmutableStack::create([$middleware1, $middleware2]);

        $this->assertSame([$middleware1, $middleware2], $stack->toArray());
    }
}
