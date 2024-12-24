<?php

declare(strict_types=1);

namespace PHPPress\Middleware\Resolve;

use InvalidArgumentException;
use PHPPress\Middleware\Exception\{Message, MiddlewareResolution};
use Psr\Container\ContainerInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Implements conversion of string class names into PSR-15 middleware.
 *
 * Key features:
 * - Container-based middleware resolution.
 * - String class name support.
 * - PSR-15 middleware compatibility.
 * - Handler resolution support.
 * - Type validation.
 *
 * ```php
 * $middleware = new StringMiddleware(AuthMiddleware::class, $container);
 * ```
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
readonly class StringMiddleware implements MiddlewareInterface
{
    /**
     * Creates a new instance of the string middleware.
     *
     * @param string $className The class name to resolve.
     * @param ContainerInterface $container The container to resolve from.
     */
    public function __construct(private string $className, private ContainerInterface $container) {}

    /**
     * Resolves and processes the middleware from container.
     *
     * @param ServerRequestInterface $request The request to process.
     * @param RequestHandlerInterface $handler The request handler.
     *
     * @throws InvalidArgumentException If class not found or invalid type.
     *
     * @return ResponseInterface The response from the middleware.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->container->has($this->className) === false) {
            throw new MiddlewareResolution(Message::NOT_FOUND_IN_CONTAINER->getMessage($this->className));
        }

        $instance = $this->container->get($this->className);

        if ($instance instanceof MiddlewareInterface) {
            return $instance->process($request, $handler);
        }

        if ($instance instanceof RequestHandlerInterface) {
            return $instance->handle($request);
        }

        throw new MiddlewareResolution(
            Message::INVALID_RESOLVE_STRING_MIDDLEWARE->getMessage(
                $this->className,
                MiddlewareInterface::class,
                RequestHandlerInterface::class,
            ),
        );
    }
}
