<?php

declare(strict_types=1);

namespace PHPPress\Middleware\Resolve;

use PHPPress\Middleware\Exception\{Message, MiddlewareResolution};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

use function get_debug_type;

/**
 * Implements conversion of callable functions into PSR-15 middleware.
 *
 * Key features:
 * - Callable to middleware adapter.
 * - Response type validation.
 * - PSR-15 middleware compatibility.
 * - Type-safe response handling.
 *
 * ```php
 * $middleware = new CallableMiddleware(
 *     static fn(
 *         ServerRequestInterface $request,
 *         RequestHandlerInterface $handler,
 *     ): ResponseInterface => $handler->handle($request),
 * );
 * ```
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
readonly class CallableMiddleware implements MiddlewareInterface
{
    /**
     * Creates a new instance of the callable middleware.
     */
    public function __construct(private mixed $handler) {}

    /**
     * Processes an incoming request through the callable.
     *
     * @param ServerRequestInterface $request The request to process.
     * @param RequestHandlerInterface $handler The request handler.
     *
     * @throws MiddlewareResolution If callable returns invalid response type.
     *
     * @return ResponseInterface The response from the middleware.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = ($this->handler)($request, $handler);

        if ($response instanceof ResponseInterface === false) {
            throw new MiddlewareResolution(
                Message::INVALID_RESOLVE_CALLABLE_MIDDLEWARE->getMessage(
                    ResponseInterface::class,
                    get_debug_type($response),
                ),
            );
        }

        return $response;
    }
}
