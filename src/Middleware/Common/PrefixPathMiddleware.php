<?php

declare(strict_types=1);

namespace PHPPress\Middleware\Common;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Executes wrapped middleware only for paths matching a specified prefix.
 *
 * Key features:
 * - Path prefix matching.
 * - Automatic path normalization.
 * - PSR-15 middleware compatibility.
 * - Nested middleware support.
 *
 * ```php
 * $middleware = new PrefixPathMiddleware('/api', new AuthMiddleware());
 * ```
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
readonly class PrefixPathMiddleware implements MiddlewareInterface
{
    /**
     * Creates a new path prefix middleware instance.
     *
     * @param string $prefix The path prefix to match against incoming requests.
     * @param MiddlewareInterface $middleware The middleware to execute for matching paths.
     */
    public function __construct(private string $prefix, private MiddlewareInterface $middleware) {}

    /**
     * Processes an incoming request through the middleware chain.
     *
     * Executes the wrapped middleware only if the request path matches the configured prefix.
     * Otherwise, delegates to the next handler.
     *
     * @param ServerRequestInterface $request The request to process.
     * @param RequestHandlerInterface $handler The request handler.
     *
     * @return ResponseInterface The response from the middleware.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $prefix = $this->normalize($this->prefix);
        $path = $this->normalize($request->getUri()->getPath());

        if ($prefix === '/' || str_starts_with($path, $prefix)) {
            return $this->middleware->process($request, $handler);
        }

        return $handler->handle($request);
    }

    /**
     * Normalizes a path by ensuring consistent slash formatting.
     *
     * Ensures paths:
     * - Start with a forward slash.
     * - End with a forward slash (except root path).
     * - Have no duplicate slashes.
     *
     * @param string $path The path to normalize.
     *
     * @return string The normalized path.
     */
    private function normalize(string $path): string
    {
        $trimPath = trim($path, '/');

        $path = "/$trimPath";

        return ($path === '/') ? '/' : $path . '/';
    }
}
