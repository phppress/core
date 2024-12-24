<?php

declare(strict_types=1);

namespace PHPPress\Middleware;

use Closure;
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Extended PSR-15 middleware interface with chaining capabilities.
 *
 * Key features:
 * - PSR-15 compatibility
 * - Middleware chaining
 * - Path-based filtering
 * - Conditional execution
 *
 * ```php
 * $middleware = $middleware
 *     ->withMiddleware($first)
 *     ->withMiddleware($second, '/api');
 * ```
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
interface Middleware extends MiddlewareInterface, RequestHandlerInterface
{
    /**
     * Appends a middleware to the middleware stack.
     *
     * @param MiddlewareInterface $middleware The middleware instance to append.
     * @param string|null $pathPrefix Optional path prefix to restrict middleware execution.
     * @param Closure|null $closure Optional condition for middleware execution.
     *
     * @return self New middleware instance with appended middleware.
     */
    public function withMiddleware(
        MiddlewareInterface $middleware,
        string|null $pathPrefix = null,
        Closure|null $closure = null,
    ): self;
}
