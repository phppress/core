<?php

declare(strict_types=1);

namespace PHPPress\Tests\Collection;

use Generator;
use PHPPress\Collection\Exception\EmptyStack;
use PHPUnit\Framework\Attributes\Group;

use function iterator_to_array;

/**
 * Test case for the {@see \PHPPress\Collection\Stack} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('collection')]
final class StackTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate(): void
    {
        $stack = Stub\ImmutableStack::create();

        $this->assertInstanceOf(Stub\ImmutableStack::class, $stack);
        $this->assertCount(0, $stack);
    }

    public function testCreateWithItems(): void
    {
        $stack = Stub\ImmutableStack::create(['one']);

        $this->assertInstanceOf(Stub\ImmutableStack::class, $stack);
        $this->assertCount(1, $stack);
    }

    public function testCount(): void
    {
        $stack = Stub\ImmutableStack::create(
            [
                'one',
                'two',
                'three',
            ],
        );

        $this->assertCount(3, $stack);
    }

    public function testFirst(): void
    {
        $stack = Stub\ImmutableStack::create(
            [
                'one',
                'two',
                'three',
            ],
        );

        $this->assertSame('one', $stack->first());
    }

    public function testFirstWithEmptyStack(): void
    {
        $stack = Stub\ImmutableStack::create();

        $this->expectException(EmptyStack::class);
        $this->expectExceptionMessage('Empty stack: "Cannot get first item from empty collection."');

        $stack->first();
    }

    public function testGetIterator(): void
    {
        $stack = Stub\ImmutableStack::create(['one']);

        $this->assertInstanceOf(Generator::class, $stack->getIterator());
        $this->assertCount(1, iterator_to_array($stack->getIterator()));
    }

    public function testIsEmpty(): void
    {
        $stack = Stub\ImmutableStack::create();

        $this->assertTrue($stack->isEmpty());
    }

    public function testLast(): void
    {

        $stack = Stub\ImmutableStack::create(
            [
                'one',
                'three',
                'two',
            ],
        );

        $this->assertSame('two', $stack->last());
    }

    public function testLastWithEmptyStack(): void
    {
        $stack = Stub\ImmutableStack::create();

        $this->expectException(EmptyStack::class);
        $this->expectExceptionMessage('Empty stack: "Cannot get last item from empty collection."');

        $stack->last();
    }

    public function testMap(): void
    {
        $stack = Stub\ImmutableStack::create(
            [
                'one',
                'two',
                'three',
            ],
        );

        $newStack = $stack->map(
            static fn(string $item): string => $item,
        );

        $this->assertInstanceOf(Stub\ImmutableStack::class, $newStack);
        $this->assertCount(3, $newStack);
    }

    public function testPop(): void
    {
        $stack = Stub\ImmutableStack::create(
            [
                'one',
                'two',
                'three',
            ],
        );

        $newStack = $stack->pop();

        $this->assertInstanceOf(Stub\ImmutableStack::class, $newStack);
        $this->assertCount(2, $newStack);
    }

    public function testPopWithEmptyStack(): void
    {
        $stack = Stub\ImmutableStack::create();

        $this->expectException(EmptyStack::class);
        $this->expectExceptionMessage('Empty stack: "Cannot pop item from empty collection."');

        $stack->pop();
    }

    public function testPush(): void
    {
        $stack = Stub\ImmutableStack::create();

        $this->assertCount(0, $stack);

        $newStack = $stack->push('one');

        $this->assertInstanceOf(Stub\ImmutableStack::class, $newStack);
        $this->assertCount(1, $newStack);

        $newStack = $newStack->push('two');

        $this->assertCount(2, $newStack);
    }

    public function testPushWithItems(): void
    {
        $stack = Stub\ImmutableStack::create(['one']);

        $newStack = $stack->push('two');

        $this->assertInstanceOf(Stub\ImmutableStack::class, $newStack);
        $this->assertCount(2, $newStack);
    }

    public function testShift(): void
    {
        $stack = Stub\ImmutableStack::create(
            [
                'one',
                'two',
                'three',
            ],
        );

        $this->assertSame('one', $stack->first());

        $newStack = $stack->shift();

        $this->assertInstanceOf(Stub\ImmutableStack::class, $newStack);
        $this->assertCount(2, $newStack);
        $this->assertSame('two', $newStack->first());
        $this->assertSame('three', $newStack->last());
    }

    public function testShiftWithEmptyStack(): void
    {
        $stack = Stub\ImmutableStack::create();

        $this->expectException(EmptyStack::class);
        $this->expectExceptionMessage('Empty stack: "Cannot shift item from empty collection."');

        $stack->shift();
    }

    public function testShiftWithOneItem(): void
    {
        $stack = Stub\ImmutableStack::create(['one']);

        $newStack = $stack->shift();

        $this->assertInstanceOf(Stub\ImmutableStack::class, $newStack);
        $this->assertCount(0, $newStack);
    }

    public function testToArray(): void
    {
        $stack = Stub\ImmutableStack::create(
            [
                'one',
                'two',
                'three',
            ],
        );

        $this->assertSame(
            [
                'one',
                'two',
                'three',
            ],
            $stack->toArray(),
        );
    }
}
