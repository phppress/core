<?php

declare(strict_types=1);

namespace PHPPress\Middleware;

use PHPPress\Middleware\Collection\ImmutableStack;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

use function is_array;
use function is_callable;
use function is_string;
use function get_debug_type;

/**
 * Resolves various types of handlers into PSR-15 middleware instances.
 *
 * Key features:
 * - Converts RequestHandlerInterface to MiddlewareInterface.
 * - Resolves string class names using container.
 * - Handles callable conversions.
 * - Supports array of middleware.
 * - Container-based dependency resolution.
 *
 * ```php
 * $resolver = MiddlewareResolver::create($container);
 *
 * // Resolve different types
 * $middleware = $resolver->resolve(new MyHandler()); // From RequestHandler
 * $middleware = $resolver->resolve('App\Middleware\Auth'); // From class name
 * $middleware = $resolver->resolve([$mid1, $mid2]); // From array
 * ```
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
readonly class MiddlewareResolver implements Resolver
{
    private function __construct(private readonly ContainerInterface $container) {}

    /**
     * Creates a new resolver instance with container support.
     *
     * @param ContainerInterface $container DI container for class resolution.
     *
     * @return self A new instance of the middleware resolver.
     */
    public static function create(ContainerInterface $container): self
    {
        return new self($container);
    }

    /**
     * Resolves a handler into middleware.
     *
     * Supports resolution from:
     * - PSR-15 RequestHandler.
     * - PSR-15 Middleware.
     * - Callable.
     * - Array of handlers.
     * - Class name string.
     *
     * @param mixed $handler Handler to resolve.
     *
     * @throws Exception\MiddlewareResolution For invalid handlers.
     *
     * @return MiddlewareInterface Resolved middleware.
     */
    public function resolve(mixed $handler): MiddlewareInterface
    {
        if ($handler instanceof RequestHandlerInterface) {
            return $this->resolveHandler($handler);
        }

        if ($handler instanceof MiddlewareInterface) {
            return $handler;
        }

        if (is_callable($handler)) {
            return $this->resolveCallable($handler);
        }

        if (is_array($handler) && $handler !== []) {
            return $this->resolveArray($handler);
        }

        if (is_string($handler)) {
            return $this->resolveString($handler);
        }

        throw new Exception\MiddlewareResolution(
            Exception\Message::INVALID_HANDLER->getMessage(
                MiddlewareInterface::class,
                RequestHandlerInterface::class,
                get_debug_type($handler),
            ),
        );
    }

    /**
     * Resolves an array of handlers into a single middleware.
     *
     * Creates a middleware stack from the array elements.
     *
     * @param array $handlers Array of handlers to resolve,
     *
     * @return MiddlewareInterface Stack middleware.
     */
    private function resolveArray(array $handlers): MiddlewareInterface
    {
        $stack = ImmutableStack::create($handlers)->map(
            fn(mixed $handler): MiddlewareInterface => $this->resolve($handler),
        );

        $stack = new Handler\Stack($stack, new Handler\Unhandled());

        return $this->resolveHandler($stack);
    }

    /**
     * Converts a callable into middleware.
     *
     * @param callable $handler Callable to convert.
     *
     * @return MiddlewareInterface Wrapped callable middleware.
     */
    private function resolveCallable(callable $handler): MiddlewareInterface
    {
        return new Resolve\CallableMiddleware($handler);
    }

    /**
     * Adapts a PSR-15 request handler into middleware.
     *
     * @param RequestHandlerInterface $handler Handler to adapt.
     *
     * @return MiddlewareInterface Adapted handler middleware.
     */
    private function resolveHandler(RequestHandlerInterface $handler): MiddlewareInterface
    {
        return new Resolve\HandlerMiddleware($handler);
    }

    /**
     * Resolves a class name into middleware instance.
     *
     * Uses the container to instantiate the class.
     *
     * @param string $handler Class name to resolve.
     *
     * @return MiddlewareInterface Resolved middleware instance.
     */
    private function resolveString(string $handler): MiddlewareInterface
    {
        return new Resolve\StringMiddleware($handler, $this->container);
    }
}
