<?php

declare(strict_types=1);

namespace PHPPress\Middleware\Resolve;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Implements conversion of PSR-15 request handlers into middleware.
 *
 * Key features:
 * - Handler to middleware adapter.
 * - PSR-15 compatibility.
 * - Type-safe request handling.
 * - Simple delegation pattern.
 *
 * ```php
 * $middleware = new HandlerMiddleware(new FallbackHandler());
 * ```
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
readonly class HandlerMiddleware implements MiddlewareInterface
{
    /**
     * Creates a new instance of the handler middleware.
     *
     * @param RequestHandlerInterface $handler The request handler to convert into middleware.
     */
    public function __construct(private RequestHandlerInterface $handler) {}

    /**
     * Processes an incoming request through the handler.
     *
     * @param ServerRequestInterface $request The request to process.
     * @param RequestHandlerInterface $handler The request handler.
     *
     * @return ResponseInterface The response from the middleware.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->handler->handle($request);
    }
}
