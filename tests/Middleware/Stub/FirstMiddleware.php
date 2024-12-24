<?php

declare(strict_types=1);

namespace PHPPress\Tests\Middleware\Stub;

use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class FirstMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        return $response->withAddedHeader('X-Middleware', 'First');
    }
}
