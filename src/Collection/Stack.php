<?php

declare(strict_types=1);

namespace PHPPress\Collection;

use function array_key_first;
use function array_key_last;
use function array_map;
use function array_slice;
use function count;

/**
 * @template T
 * @implements \IteratorAggregate<T>
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
abstract readonly class Stack implements \Countable, \IteratorAggregate
{
    /**
     * @var T[] Internal storage of items.
     */
    private array $items;

    /**
     * Initializes a new instance of the collection.
     *
     * @param T[] $items Initial items.
     */
    final private function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * Creates a new collection instance.
     *
     * @param T[] $items Initial items.
     *
     * @return static New collection instance.
     */
    public static function create(array $items = []): static
    {
        return new static($items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function first(): mixed
    {
        if ($this->isEmpty()) {
            throw new Exception\EmptyStack(
                Exception\Message::EMPTY_STACK_FIRST_ITEM->getMessage(),
            );
        }

        return $this->items[array_key_first($this->items)];
    }

    public function getIterator(): \Generator
    {
        yield from $this->items;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function last(): mixed
    {
        if ($this->isEmpty()) {
            throw new Exception\EmptyStack(Exception\Message::EMPTY_STACK_LAST_ITEM->getMessage());
        }

        return $this->items[array_key_last($this->items)];
    }

    public function map(callable $callback): static
    {
        return new static(array_map($callback, $this->items));
    }

    public function pop(): static
    {
        if ($this->isEmpty()) {
            throw new Exception\EmptyStack(Exception\Message::EMPTY_STACK_POP->getMessage());
        }

        return new static(array_slice($this->items, 0, -1));
    }

    public function push(mixed $item): static
    {
        return new static([...$this->items, $item]);
    }

    public function shift(): static
    {
        if ($this->isEmpty()) {
            throw new Exception\EmptyStack(Exception\Message::EMPTY_STACK_SHIFT->getMessage());
        }

        return new static(array_slice($this->items, 1));
    }

    public function toArray(): array
    {
        return $this->items;
    }
}
