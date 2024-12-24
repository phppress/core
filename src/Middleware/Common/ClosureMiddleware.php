<?php

declare(strict_types=1);

namespace PHPPress\Middleware\Common;

use Closure;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Implements conditional middleware execution based on a callback condition.
 *
 * Key features:
 * - Wraps another middleware.
 * - Executes middleware only if condition returns `true`.
 * - Type-safe condition evaluation.
 * - PSR-15 middleware compatibility.
 *
 * ```php
 * $middleware = new ClosureMiddleware(
 *     new AuthMiddleware(),
 *     static fn(ServerRequestInterface $request) => $request->hasHeader('X-API-Key')
 * );
 * ```
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
readonly class ClosureMiddleware implements MiddlewareInterface
{
    /**
     * Creates a new conditional middleware instance.
     *
     * @param MiddlewareInterface $middleware The middleware to execute conditionally.
     * @param Closure $condition Callback that determines if middleware executes.
     */
    public function __construct(private MiddlewareInterface $middleware, private Closure $condition) {}

    /**
     * Processes the request through the middleware if condition is met.
     *
     * @param ServerRequestInterface $request The request to process.
     * @param RequestHandlerInterface $handler The request handler.
     *
     * @return ResponseInterface The response from the middleware.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $condition = $this->condition;

        if ($condition($request) === true) {
            return $this->middleware->process($request, $handler);
        }

        return $handler->handle($request);
    }
}
