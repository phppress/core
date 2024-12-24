<?php

declare(strict_types=1);

namespace PHPPress\Middleware\Handler;

use PHPPress\Middleware\Exception\{Message, UnhandledRequest};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Implements a fallback handler that throws an exception for unprocessed requests.
 *
 * Key features:
 * - Final fallback handler.
 * - Exception-based error reporting.
 * - PSR-15 handler compatibility.
 * - Clear error messaging.
 * - Type-safe request handling.
 *
 * ```php
 * $stack = new Stack(
 *     ImmutableStack::create([$middleware1, $middleware2]),
 *     new Unhandled()
 * );
 *
 * try {
 *     $response = $stack->handle($request);
 * } catch (UnhandledRequest $e) {
 *     // Handle unprocessed request
 * }
 * ```
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class Unhandled implements RequestHandlerInterface
{
    /**
     * Handles an unprocessed request by throwing an exception.
     *
     * @param ServerRequestInterface $request The request to process.
     *
     * @throws UnhandledRequest Always thrown to indicate request was not processed.
     *
     * @return ResponseInterface The response from the middleware.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new UnhandledRequest(Message::NO_MIDDLEWARE_HANDLED_REQUEST->getMessage());
    }
}
