<?php

declare(strict_types=1);

namespace PHPPress\Middleware\Handler;

use PHPPress\Middleware\Collection\MiddlewareStack;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Implements an immutable PSR-15 request handler for middleware processing.
 *
 * Key features:
 * - Sequential middleware processing.
 * - Immutable middleware stack.
 * - PSR-15 handler compatibility.
 * - Fallback handler support.
 * - Type-safe middleware execution.
 *
 * ```php
 * $stack = new Stack(ImmutableStack::create([$middleware1, $middleware2]), new FallbackHandler());
 * $response = $stack->handle($request);
 * ```
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
readonly class Stack implements RequestHandlerInterface
{
    /**
     * Creates a new middleware stack handler.
     *
     * @param MiddlewareStack $stack Array of middleware to process sequentially.
     * @param RequestHandlerInterface $handler Fallback handler when stack is empty.
     */
    public function __construct(private MiddlewareStack $stack, private RequestHandlerInterface $handler) {}

    /**
     * Processes the request through the middleware stack.
     *
     * Executes middleware sequentially, falling back to the handler when the stack is exhausted.
     *
     * @param ServerRequestInterface $request The request to process.
     *
     * @return ResponseInterface The response from the middleware.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->stack->isEmpty()) {
            return $this->handler->handle($request);
        }

        $middleware = $this->stack->first();
        $nextHandler = new self($this->stack->shift(), $this->handler);

        return $middleware->process($request, $nextHandler);
    }
}
