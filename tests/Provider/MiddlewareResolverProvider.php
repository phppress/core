<?php

declare(strict_types=1);

namespace PHPPress\Tests\Provider;

use HttpSoft\Message\Response;
use PHPPress\Tests\Middleware\Stub\{DummyHandler, FirstMiddleware, RequestHandler, SecondMiddleware, ThirdMiddleware};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Provider for the {@see \PHPPress\Tests\Helper\MiddlewareResolverTest} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class MiddlewareResolverProvider
{
    /**
     * Data provider for the resolve method.
     *
     * @phpstan-return array<array{string, string}>
     */
    public static function resolve(): array
    {
        return [
            'middleware-class' => [FirstMiddleware::class],
            'middleware-object' => [new FirstMiddleware()],
            'request-handler-class' => [RequestHandler::class],
            'request-handler-object' => [new RequestHandler()],
            'callable-without-args' => [
                static fn(): ResponseInterface => new Response(),
            ],
            'callable-with-signature-as-request-handler-handle' => [
                static fn(ServerRequestInterface $request): ResponseInterface => new Response(),
            ],
            'callable-with-signature-as-middleware-process' => [
                static function (ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
                    return $handler->handle($request);
                },
            ],
            'array-callable-without-args' => [
                [new DummyHandler(), 'handler'],
            ],
            'array-static-callable-without-args' => [
                [DummyHandler::class, 'staticHandler'],
            ],
            'array-static-callable-with-args' => [
                [new DummyHandler(), 'process'],
            ],
            'array-middleware-classes' => [
                [
                    FirstMiddleware::class,
                    SecondMiddleware::class,
                    ThirdMiddleware::class,
                ],
            ],
            'array-middleware-request-handle-classes' => [
                [
                    FirstMiddleware::class,
                    SecondMiddleware::class,
                    ThirdMiddleware::class,
                    RequestHandler::class,
                ],
            ],
            'array-middleware-objects' => [
                [
                    new FirstMiddleware(),
                    new SecondMiddleware(),
                    new ThirdMiddleware(),
                ],
            ],
            'array-middleware-request-handle-objects' => [
                [
                    new FirstMiddleware(),
                    new SecondMiddleware(),
                    new ThirdMiddleware(),
                    new RequestHandler(),
                ],
            ],
            'array-middleware-request-handle-classes-objects' => [
                [
                    new FirstMiddleware(),
                    SecondMiddleware::class,
                    new ThirdMiddleware(),
                    RequestHandler::class,
                ],
            ],

            'array-middleware-callable-request-handle-classes-objects' => [
                [
                    FirstMiddleware::class,
                    [new SecondMiddleware(), 'process'],
                    static function (
                        ServerRequestInterface $request,
                        RequestHandlerInterface $handler,
                    ): ResponseInterface {
                        return (new ThirdMiddleware())->process($request, $handler);
                    },
                    new RequestHandler(),
                ],
            ],
        ];
    }
}
