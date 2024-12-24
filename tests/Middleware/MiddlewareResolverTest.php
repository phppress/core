<?php

declare(strict_types=1);

namespace PHPPress\Tests\Middleware;

use HttpSoft\Message\ServerRequest;
use PHPPress\Di\Container;
use PHPPress\Middleware\{MiddlewareDispatcher, MiddlewareResolver};
use PHPPress\Middleware\Exception\MiddlewareResolution;
use PHPPress\Tests\Provider\MiddlewareResolverProvider;
use PHPUnit\Framework\Attributes\{DataProviderExternal, Group};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

/**
 * Test case for the {@see \PHPPress\Middleware\MiddlewareResolver} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('middleware')]
final class MiddlewareResolverTest extends \PHPUnit\Framework\TestCase
{
    public function testMiddlewareInterfaceIsReturnedAsIs(): void
    {
        $originalMiddleware = new class implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $handler->handle($request);
            }
        };

        $resolvedMiddleware = MiddlewareResolver::create(new Container())->resolve($originalMiddleware);

        $this->assertSame(
            $originalMiddleware,
            $resolvedMiddleware,
            'MiddlewareInterface instances should be returned without modification',
        );
    }

    public function testNonEmptyArrayIsResolved(): void
    {
        $handler = $this->createHandler();
        $middleware = MiddlewareResolver::create(new Container())->resolve([$handler]);

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    #[DataProviderExternal(MiddlewareResolverProvider::class, 'resolve')]
    public function testResolve(mixed $handler): void
    {
        $handler = $this->createHandler();
        $request = $this->createRequest();

        $middleware = MiddlewareResolver::create(new Container())->resolve($handler);

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(ResponseInterface::class, $middleware->process($request, $handler));
    }

    public function testResolveUsingHandlerCallable(): void
    {
        $handler = $this->createHandler();
        $request = $this->createRequest();

        $middleware = MiddlewareResolver::create(new Container())->resolve(
            static function (
                ServerRequestInterface $request,
                RequestHandlerInterface $handler,
            ): ResponseInterface {
                return $handler->handle($request);
            },
        );

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(ResponseInterface::class, $middleware->process($request, $handler));
    }

    public function testResolveUsingHandlerString(): void
    {
        $handler = $this->createHandler();
        $request = $this->createRequest();

        $middleware = MiddlewareResolver::create(new Container())->resolve(Stub\RequestHandler::class);

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(ResponseInterface::class, $middleware->process($request, $handler));
    }

    public function testResolveUsingMiddlewareString(): void
    {
        $handler = $this->createHandler();
        $request = $this->createRequest();

        $middleware = MiddlewareResolver::create(new Container())->resolve(Stub\FirstMiddleware::class);

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
        $this->assertInstanceOf(ResponseInterface::class, $middleware->process($request, $handler));
    }

    public function testThrowsExceptionWithInvalidHandler(): void
    {
        $resolver = MiddlewareResolver::create(new Container());

        $invalidHandler = new stdClass();

        $this->expectException(MiddlewareResolution::class);
        $this->expectExceptionMessage(
            'Middleware resolution: "Invalid middleware handler. Expected a string, an array, a callable, an instance of Psr\Http\Server\MiddlewareInterface or Psr\Http\Server\RequestHandlerInterface, but got: stdClass."',
        );

        $resolver->resolve($invalidHandler);
    }

    public function testThrowsExceptionWithInvalidHandlerUsingEmptyArrayValue(): void
    {
        $resolver = MiddlewareResolver::create(new Container());

        $invalidHandler = [];

        $this->expectException(MiddlewareResolution::class);
        $this->expectExceptionMessage(
            'Middleware resolution: "Invalid middleware handler. Expected a string, an array, a callable, an instance of Psr\Http\Server\MiddlewareInterface or Psr\Http\Server\RequestHandlerInterface, but got: array."',
        );

        $resolver->resolve($invalidHandler);
    }

    public function testThrowsExceptionWithInvalidHandlerUsingCallable(): void
    {
        $resolver = MiddlewareResolver::create(new Container());

        $handler = $this->createHandler();
        $request = $this->createRequest();
        $invalidHandler = static fn(): null => null;

        $this->expectException(MiddlewareResolution::class);
        $this->expectExceptionMessage(
            'Middleware resolution: "Callable middleware must return an instance of Psr\Http\Message\ResponseInterface. Got: null."',
        );

        $resolver->resolve($invalidHandler)->process($request, $handler);
    }

    public function testThorwsExceptionWithInvalidHandlerUsingString(): void
    {
        $resolver = MiddlewareResolver::create(new Container());

        $handler = $this->createHandler();
        $request = $this->createRequest();
        $invalidHandler = 'stdClass';

        $this->expectException(MiddlewareResolution::class);
        $this->expectExceptionMessage(
            'Middleware resolution: "Middleware class "stdClass" must implement Psr\Http\Server\MiddlewareInterface or Psr\Http\Server\RequestHandlerInterface."',
        );

        $resolver->resolve($invalidHandler)->process($request, $handler);
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
