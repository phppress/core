<?php

declare(strict_types=1);

namespace PHPPress\Tests\Middleware\Stub;

use HttpSoft\Message\Response;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class RequestHandler implements RequestHandlerInterface
{
    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = new Response(200, ['X-Request-Handler' => 'true']);

        $response->getBody()->write('Request Handler Content');

        return $response;
    }
}
