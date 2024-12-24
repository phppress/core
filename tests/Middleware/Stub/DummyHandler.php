<?php

declare(strict_types=1);

namespace PHPPress\Tests\Middleware\Stub;

use HttpSoft\Message\Response;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class DummyHandler
{
    /**
     * @return Response
     */
    public static function staticHandler(): response
    {
        return new Response();
    }

    public static function invalidStaticHandler(): stdClass
    {
        return new stdClass();
    }

    public function handler(): Response
    {
        return new Response();
    }

    public function invalidHandler(): null
    {
        return null;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }

    public function invalidProcess(ServerRequestInterface $request, RequestHandlerInterface $handler): bool
    {
        return false;
    }
}
