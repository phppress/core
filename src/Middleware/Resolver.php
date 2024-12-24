<?php

declare(strict_types=1);

namespace PHPPress\Middleware;

use Psr\Http\Server\MiddlewareInterface;

/**
 * Interface for resolving various handler types into PSR-15 middleware instances.
 *
 * Key features:
 * - PSR-15 RequestHandler resolution.
 * - PSR-15 Middleware resolution.
 * - Callable handler resolution.
 * - Array handler resolution .
 * - String class name resolution.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
interface Resolver
{
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
    public function resolve(mixed $handler): MiddlewareInterface;
}
