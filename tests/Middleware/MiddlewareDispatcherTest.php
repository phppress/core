<?php

declare(strict_types=1);

namespace PHPPress\Tests\Middleware;

use HttpSoft\Message\ServerRequest;
use PHPPress\Middleware\Exception\UnhandledRequest;
use PHPPress\Middleware\MiddlewareDispatcher;
use PHPPress\Tests\Provider\MiddlewareDispatcherProvider;
use PHPUnit\Framework\Attributes\{DataProviderExternal, Group};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Test case for the {@see \PHPPress\Middleware\MiddlewareDispatcher} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('middleware')]
final class MiddlewareDispatcherTest extends \PHPUnit\Framework\TestCase
{
    public function testConditional(): void
    {
        $handler = $this->createHandler();
        $middlewareDispatcher = $this->createMiddlewareDispatcher();
        $request = $this->createRequest();


        // Test condition true
        $originalDispatcher = $middlewareDispatcher
            ->withMiddleware(new Stub\PathMiddleware(), closure: static fn(): bool => true);

        $response = $originalDispatcher->process($request, $handler);

        $this->assertTrue($response->hasHeader('X-Path-Prefix'));

        // Test condition false
        $newDispatcher = $middlewareDispatcher
            ->withMiddleware(new Stub\PathMiddleware(), closure: static fn(): bool => false);

        $response = $newDispatcher->process($request, $handler);

        $this->assertFalse($response->hasHeader('X-Path-Prefix'));
    }

    public function testHandleWithMiddlewareThatReplacesRequestHandler(): void
    {
        $request = $this->createRequest();
        $middlewareDispatcher = $this->createMiddlewareDispatcher()->withMiddleware(
            new class implements MiddlewareInterface {
                public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
                {
                    return (new Stub\RequestHandler())->handle($request);
                }
            }
        );

        $response = $middlewareDispatcher->handle($request);

        $this->assertTrue($response->hasHeader('X-Request-Handler'));
    }

    public function testImmutability(): void
    {
        $middlewareDispatcher = $this->createMiddlewareDispatcher();

        $this->assertNotSame($middlewareDispatcher, $middlewareDispatcher->withMiddleware(new Stub\FirstMiddleware()));
    }

    #[DataProviderExternal(MiddlewareDispatcherProvider::class, 'matchesPathPrefix')]
    public function testMatchesPathPrefix(string $pathPrefix, string $requestUriPath): void
    {
        $handler = $this->createHandler();
        $middlewareDispatcher = $this->createMiddlewareDispatcher()
            ->withMiddleware(new Stub\PathMiddleware(), $pathPrefix);
        $request = $this->createRequest();

        $request = $request->withUri($request->getUri()->withPath($requestUriPath));
        $response = $middlewareDispatcher->process($request, $handler);

        $this->assertTrue($response->hasHeader('X-Path-Prefix'));
        $this->assertTrue($response->hasHeader('X-Request-Handler'));
    }

    #[DataProviderExternal(MiddlewareDispatcherProvider::class, 'notMatchesPathPrefix')]
    public function testNotMatchesPathPrefix(string $pathPrefix, string $requestUriPath): void
    {
        $handler = $this->createHandler();
        $middlewareDispatcher = $this->createMiddlewareDispatcher()
            ->withMiddleware(new Stub\PathMiddleware(), $pathPrefix);
        $request = $this->createRequest();

        $request = $request->withUri($request->getUri()->withPath($requestUriPath));
        $response = $middlewareDispatcher->process($request, $handler);

        $this->assertFalse($response->hasHeader('X-Path-Prefix'));
        $this->assertTrue($response->hasHeader('X-Request-Handler'));
    }

    public function testOrderByAsc(): void
    {
        $handler = $this->createHandler();
        $request = $this->createRequest();
        $middlewareDispatcher = $this->createMiddlewareDispatcher()
            ->withMiddleware(new Stub\FirstMiddleware())
            ->withMiddleware(new Stub\SecondMiddleware())
            ->withMiddleware(new Stub\ThirdMiddleware());

        $response = $middlewareDispatcher->process($request, $handler);

        $this->assertTrue($response->hasHeader('X-Request-Handler'));
        $this->assertSame(['Third', 'Second', 'First'], $response->getHeader('X-Middleware'));
    }

    public function testOrderByDesc(): void
    {
        $handler = $this->createHandler();
        $request = $this->createRequest();
        $middlewareDispatcher = $this->createMiddlewareDispatcher()
            ->withMiddleware(new Stub\ThirdMiddleware())
            ->withMiddleware(new Stub\SecondMiddleware())
            ->withMiddleware(new Stub\FirstMiddleware());

        $response = $middlewareDispatcher->process($request, $handler);

        $this->assertTrue($response->hasHeader('X-Request-Handler'));
        $this->assertSame(['First', 'Second', 'Third'], $response->getHeader('X-Middleware'));
    }

    public function testThrowsExceptionWhenNoMiddlewareHandlesRequest(): void
    {
        $request = $this->createRequest();

        $this->expectException(UnhandledRequest::class);
        $this->expectExceptionMessage('Unhandled request: "No middleware handled the request."');

        $this->createMiddlewareDispatcher()->handle($request);
    }

    private function createHandler(): Stub\RequestHandler
    {
        return new Stub\RequestHandler();
    }

    private function createMiddlewareDispatcher(): MiddlewareDispatcher
    {
        return new MiddlewareDispatcher();
    }

    private function createRequest(): ServerRequestInterface
    {
        return new ServerRequest();
    }
}
