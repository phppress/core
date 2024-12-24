<?php

declare(strict_types=1);

namespace PHPPress\Middleware\Collection;

use Generator;
use Psr\Http\Server\MiddlewareInterface;
use RuntimeException;

use function array_slice;
use function count;

/**
 * Implements an immutable stack data structure for PSR-15 middleware.
 *
 * Key features:
 * - Immutable operations (all operations return new instances).
 * - Stack-like operations (push, pop, shift).
 * - Implements Countable and IteratorAggregate interfaces.
 * - Type-safe middleware storage.
 *
 * ```php
 * $stack = ImmutableStack::create([$middleware1, $middleware2]);
 * $newStack = $stack->push($middleware3);
 * $firstMiddleware = $stack->first();
 * ```
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
readonly class ImmutableStack implements \Countable, \IteratorAggregate
{
    /**
     * Initializes a new instance of the stack.
     *
     * @param array $items Initial middleware items.
     *
     * @phpstan-param MiddlewareInterface[] $items
     */
    private function __construct(private array $items) {}

    /**
     * Creates a new stack instance.
     *
     * @param array $items Initial middleware items.
     *
     * @return self New stack instance.
     *
     * @phpstan-param MiddlewareInterface[] $items
     */
    public static function create(array $items = []): self
    {
        return new self($items);
    }

    /**
     * Returns the number of items in the stack.
     *
     * @return int Number of items.
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Returns the first middleware in the stack.
     *
     * @throws RuntimeException If the stack is empty.
     *
     * @return MiddlewareInterface The first middleware.
     */
    public function first(): MiddlewareInterface
    {
        if ($this->isEmpty()) {
            throw new RuntimeException('Cannot get first item from empty stack.');
        }

        return $this->items[0];
    }

    /**
     * Provides iteration over the stack items.
     *
     * @return Generator<MiddlewareInterface> Generator for stack items.
     */
    public function getIterator(): Generator
    {
        yield from $this->items;
    }

    /**
     * Checks if the stack is empty.
     *
     * @return bool True if stack is empty, false otherwise.
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Returns the last middleware in the stack.
     *
     * @throws RuntimeException If the stack is empty.
     *
     * @return MiddlewareInterface The last middleware.
     */
    public function last(): MiddlewareInterface
    {
        if ($this->isEmpty()) {
            throw new RuntimeException('Cannot get last item from empty stack.');
        }

        return $this->items[$this->count() - 1];
    }

    /**
     * Maps each item in the stack using a callback function.
     *
     * @param callable $callback Function to apply to each item.
     *
     * @return self New stack with mapped items.
     */
    public function map(callable $callback): self
    {
        $items = [];

        foreach ($this->items as $key => $item) {
            $items[$key] = $callback($item, $key);
        }

        return new self($items);
    }

    /**
     * Removes and returns a new stack without the last item.
     *
     * @throws RuntimeException If the stack is empty.
     *
     * @return self New stack without the last item.
     */
    public function pop(): self
    {
        if ($this->isEmpty()) {
            throw new RuntimeException('Cannot pop from empty stack.');
        }

        return new self(array_slice($this->items, 0, -1));
    }

    /**
     * Adds a new middleware to the end of the stack.
     *
     * @param MiddlewareInterface $item The middleware to add.
     *
     * @return self New stack with the added item.
     */
    public function push(MiddlewareInterface $item): self
    {
        return new self([...$this->items, $item]);
    }

    /**
     * Removes and returns a new stack without the first item.
     *
     * @throws RuntimeException If the stack is empty.
     *
     * @return self New stack without the first item.
     */
    public function shift(): self
    {
        if ($this->isEmpty()) {
            throw new RuntimeException('Cannot shift from empty stack.');
        }

        return new self(array_slice($this->items, 1));
    }

    /**
     * Returns the stack items as an array.
     *
     * @return array Array of middleware items.
     *
     * @phpstan-return MiddlewareInterface[]
     */
    public function toArray(): array
    {
        return $this->items;
    }
}
