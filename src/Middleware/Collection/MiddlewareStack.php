<?php

declare(strict_types=1);

namespace PHPPress\Middleware\Collection;

use PHPPress\Collection\Stack;
use Psr\Http\Server\MiddlewareInterface;

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
 * @extends Stack<MiddlewareInterface>
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
readonly class MiddlewareStack extends Stack {}
