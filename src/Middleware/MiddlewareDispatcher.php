<?php

declare(strict_types=1);

namespace PHPPress\Middleware;

use Closure;
use InvalidArgumentException;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Dispatches requests through an immutable middleware stack with optional path prefixing.
 *
 * Key features:
 * - Immutable middleware stack management.
 * - Path-based middleware filtering.
 * - Conditional middleware execution.
 * - PSR-15 compatibility.
 * - Chainable middleware addition.
 *
 * ```php
 * $dispatcher = new MiddlewareDispatcher();
 *
 * // Add middleware with optional path prefix
 * $dispatcher = $dispatcher
 *     ->withMiddleware(new AuthMiddleware(), '/admin')
 *     ->withMiddleware(new CacheMiddleware())
 *     ->withMiddleware(
 *         new RateLimitMiddleware(),
 *         '/api',
 *         fn($request) => $request->hasHeader('X-API-Key')
 *     );
 * ```
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
class MiddlewareDispatcher implements Middleware
{
    private Collection\MiddlewareStack $stack {
        get => $this->stack ?? Collection\MiddlewareStack::create();
        set => $this->stack = $value;
    }

    /**
     * Handles the request by processing it through the middleware stack.
     *
     * The request is processed through all middleware in the stack. If no middleware handles the request, an
     * UnhandledRequestException is thrown.
     *
     * @param ServerRequestInterface $request The request to handle.
     *
     * @return ResponseInterface The processed response.
     *
     * @throws InvalidArgumentException When no middleware handles the request.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->process($request, new Handler\Unhandled());
    }

    /**
     * Processes the request through the middleware stack.
     *
     * Executes each middleware in sequence, passing control to the next handler if the current middleware doesn't
     * generate a response.
     *
     * @param ServerRequestInterface $request The request to process.
     * @param RequestHandlerInterface $handler The request handler.
     *
     * @return ResponseInterface The processed response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->createHandler($handler)->handle($request);
    }

    /**
     * Creates a new instance with the given middleware appended.
     *
     * The middleware can be optionally restricted to:
     * - A specific URL path prefix.
     * - A runtime condition via closure.
     *
     * @param MiddlewareInterface $middleware Middleware to append.
     * @param string|null $pathPrefix Optional path restriction.
     * @param Closure|null $closure Optional runtime condition.
     *
     * @return self A new instance with the appended middleware.
     */
    public function withMiddleware(
        MiddlewareInterface $middleware,
        string|null $pathPrefix = null,
        Closure|null $closure = null,
    ): self {
        if ($pathPrefix !== null && $pathPrefix !== '/') {
            $middleware = new Common\PrefixPathMiddleware($pathPrefix, $middleware);
        }

        if ($closure !== null) {
            $middleware = new Common\ClosureMiddleware($middleware, $closure);
        }

        $new = clone $this;

        $new->stack = $this->stack->push($middleware);

        return $new;
    }

    /**
     * Creates a handler wrapping the current middleware stack.
     *
     * @param RequestHandlerInterface $handler The request handler.
     *
     * @return RequestHandlerInterface Configured stack handler,
     */
    private function createHandler(RequestHandlerInterface $handler): RequestHandlerInterface
    {
        return new Handler\Stack($this->stack, $handler);
    }
}
