<?php

declare(strict_types=1);

namespace PHPPress\Tests\Http\Emitter;

use HttpSoft\Message\Response;
use PHPPress\Exception\InvalidArgument;
use PHPPress\Http\Emitter\Exception\{HeadersAlreadySent, OutputAlreadySent};
use PHPPress\Http\Emitter\SapiEmitter;
use PHPPress\Tests\Http\Emitter\Stub\HTTPFunctions;
use PHPPress\Tests\Provider\EmitterProvider;
use PHPUnit\Framework\Attributes\{DataProviderExternal, Group};
use Psr\Http\Message\{ResponseInterface, StreamInterface};

/**
 * Test case for the {@see SapiEmitter} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
#[Group('http')]
final class SapiEmitterTest extends \PHPUnit\Framework\TestCase
{
    private array $mocks = [];

    public function setUp(): void
    {
        HTTPFunctions::reset();
    }

    public static function tearDownAfterClass(): void
    {
        HTTPFunctions::reset();
    }

    public function testBasicWithoutHeaders(): void
    {
        $response = $this->createResponse();

        new SapiEmitter()->emit($response);

        $this->assertSame(200, Httpfunctions::http_response_code());
        $this->assertCount(0, Httpfunctions::headers_list());
        $this->assertSame([], Httpfunctions::headers_list());
        $this->assertSame('HTTP/1.1 200 OK', $this->httpResponseStatusLine($response));
        $this->expectOutputString('');
    }

    public function testBasicWithBodyTrue(): void
    {
        $response = $this->createResponse($code = 404, ['X-Test' => 'test'], 'Page not found', '2');

        new SapiEmitter()->emit($response, true);

        $this->assertSame($code, Httpfunctions::http_response_code());
        $this->assertCount(1, Httpfunctions::headers_list());
        $this->assertSame(['X-Test: test'], Httpfunctions::headers_list());
        $this->assertSame('HTTP/2 404 Not Found', $this->httpResponseStatusLine($response));
        $this->expectOutputString('');
    }

    public function testBasicWithCustomStatusAndHeaders(): void
    {
        $response = $this->createResponse($code = 404, ['X-Test' => 'test'], $contents = 'Page not found', '2');

        new SapiEmitter()->emit($response);

        $this->assertSame($code, Httpfunctions::http_response_code());
        $this->assertCount(1, Httpfunctions::headers_list());
        $this->assertSame(['X-Test: test'], Httpfunctions::headers_list());
        $this->assertSame('HTTP/2 404 Not Found', $this->httpResponseStatusLine($response));
        $this->expectOutputString($contents);
    }

    #[DataProviderExternal(EmitterProvider::class, 'reasonPhrase')]
    public function testBasicWithStatusReasonPhraseLineFormat(
        int $code,
        string $reasonPhrase,
        string $expectedHeader,
    ): void {
        $response = $this->createResponse($code);
        $response = $response->withStatus($code, $reasonPhrase);

        (new SapiEmitter())->emit($response);

        $this->assertSame($expectedHeader, $this->httpResponseStatusLine($response));
        $this->assertSame($code, Httpfunctions::http_response_code());
    }

    #[DataProviderExternal(EmitterProvider::class, 'body')]
    public function testBody(string $contents, array $expected, int|null $buffer, int|null $first, int|null $last): void
    {
        $isContentRange = (is_int($first) && is_int($last));
        $outputString = $isContentRange ? implode('', $expected) : $contents;
        $headers = $isContentRange ? ['Content-Range' => "bytes $first-$last/*"] : [];
        $expectedHeaders = $isContentRange ? ["Content-Range: bytes $first-$last/*"] : [];

        $response = $this->createResponse(200, $headers, $contents);

        new SapiEmitter($buffer)->emit($response);

        $this->assertSame(200, Httpfunctions::http_response_code());
        $this->assertCount(count($expectedHeaders), Httpfunctions::headers_list());
        $this->assertSame($expectedHeaders, Httpfunctions::headers_list());
        $this->expectOutputString($outputString);
    }

    public function testBodyWithEmptyContent(): void
    {
        $response = $this->createResponse(200, ['Content-Range' => 'bytes 0-3/8'], '');

        new SapiEmitter(8192)->emit($response);

        $this->assertSame(200, Httpfunctions::http_response_code());
        $this->assertCount(1, Httpfunctions::headers_list());
        $this->assertSame(['Content-Range: bytes 0-3/8'], Httpfunctions::headers_list());
        $this->expectOutputString('');
    }

    public function testBodyUsingRangeWithResponseEmptyStream(): void
    {
        $stream = $this->createMock(StreamInterface::class);

        $stream->method('isSeekable')->willReturn(true);
        $stream->method('eof')->willReturn(false, true);
        $stream->method('read')->willReturn('');
        $stream->method('isReadable')->willReturn(true);
        $stream->expects($this->once())->method('seek')->with(0);

        $response = $this->createResponse(200, ['Content-Range' => 'bytes 0-3/8'], $stream);

        new SapiEmitter(8192)->emit($response);

        $this->expectOutputString('');
        $this->assertSame(1, HTTPFunctions::getFlushTimes());
    }

    public function testBodyWithResponseNotReadableStream(): void
    {
        $response = new Response(200, [], fopen('php://output', 'c'));

        $this->assertSame('php://output', $response->getBody()->getMetadata('uri'));
        $this->assertFalse($response->getBody()->isReadable());

        new SapiEmitter()->emit($response);

        $this->expectOutputString('');

        new SapiEmitter(8192)->emit($response);

        $this->expectOutputString('');

        $response = $response->withHeader('Content-Range', 'bytes 0-3/8');

        new SapiEmitter(8192)->emit($response);

        $this->assertSame(['Content-Range: bytes 0-3/8'], Httpfunctions::headers_list());
        $this->expectOutputString('');
    }

    public function testBufferLength(): void
    {
        $response = $this->createResponse();

        new SapiEmitter()->emit($response);

        $this->assertSame(200, Httpfunctions::http_response_code());
        $this->assertCount(0, Httpfunctions::headers_list());
        $this->assertSame([], Httpfunctions::headers_list());
        $this->assertSame('HTTP/1.1 200 OK', $this->httpResponseStatusLine($response));
        $this->expectOutputString('');
    }

    public function testBufferLengthWithCustomValue(): void
    {
        $response = $this->createResponse();

        new SapiEmitter(8192)->emit($response);

        $this->assertSame(200, Httpfunctions::http_response_code());
        $this->assertCount(0, Httpfunctions::headers_list());
        $this->assertSame([], Httpfunctions::headers_list());
        $this->assertSame('HTTP/1.1 200 OK', $this->httpResponseStatusLine($response));
        $this->expectOutputString('');
    }

    public function testBufferLengthWithNegativeValue(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage(
            'Invalid argument: "Buffer length for `PHPPress\Http\Emitter\SapiEmitter` must be greater than zero; received `-1`."',
        );

        new SapiEmitter(-1);
    }

    public function testBufferLengthWithRange(): void
    {
        $response = $this->createResponse(200, ['Content-Range' => 'bytes 0-3/8'], 'Contents');

        new SapiEmitter(1)->emit($response);

        $this->assertSame(200, Httpfunctions::http_response_code());
        $this->assertCount(1, Httpfunctions::headers_list());
        $this->assertSame(['Content-Range: bytes 0-3/8'], Httpfunctions::headers_list());
        $this->assertSame('HTTP/1.1 200 OK', $this->httpResponseStatusLine($response));
        $this->expectOutputString('Cont');
    }

    public function testBufferLengthWithRangeAndBodyTrue(): void
    {
        $emitter = new SapiEmitter(1);
        $response = $this->createResponse($code = 200, ['Content-Range' => 'bytes 0-3/8'], 'Contents');
        $emitter->emit($response, true);

        $this->assertSame($code, Httpfunctions::http_response_code());
        $this->assertCount(1, Httpfunctions::headers_list());
        $this->assertSame(['Content-Range: bytes 0-3/8'], Httpfunctions::headers_list());
        $this->assertSame('HTTP/1.1 200 OK', $this->httpResponseStatusLine($response));
        $this->expectOutputString('');
    }

    public function testBufferLengthWithResponseAndSeveralArguments(): void
    {
        $response = $this->createResponse(404, ['X-Test' => 'test'], 'Page not found', '2');

        new SapiEmitter(2)->emit($response);

        $this->assertSame(404, Httpfunctions::http_response_code());
        $this->assertCount(1, Httpfunctions::headers_list());
        $this->assertSame(['X-Test: test'], Httpfunctions::headers_list());
        $this->assertSame('HTTP/2 404 Not Found', $this->httpResponseStatusLine($response));
        $this->expectOutputString('Page not found');
    }

    public function testBufferLengthWithZeroValue(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage(
            'Invalid argument: "Buffer length for `PHPPress\Http\Emitter\SapiEmitter` must be greater than zero; received `0`."',
        );

        new SapiEmitter(0);
    }

    public function testHeaderNormalization(): void
    {
        $response = $this->createResponse(200, ['CONTENT-Type' => 'text/plain', 'X-Custom-HEADER' => 'value']);

        new SapiEmitter()->emit($response);

        $this->assertSame(
            [
                'Content-Type: text/plain',
                'X-Custom-Header: value',
            ],
            HTTPFunctions::headers_list(),
        );
    }

    public function testHeaderWithMultipleSetCookie(): void
    {
        $response = $this->createResponse(200, ['Set-Cookie' => ['cookie1=value1', 'cookie2=value2']]);

        new SapiEmitter()->emit($response);

        $this->assertSame(
            [
                'Set-Cookie: cookie1=value1',
                'Set-Cookie: cookie2=value2',
            ],
            HTTPFunctions::headers_list(),
        );
    }

    public function testHeaderWithResponseSeveralAddHeaderAndSetCookie(): void
    {
        $response = $this->createResponse(200, ['Content-Type' => ['text/plain']]);

        $response = $response
            ->withAddedHeader('Set-Cookie', 'key-1=value-1')
            ->withAddedHeader('X-Custom', 'value1')
            ->withAddedHeader('X-Custom', 'value2');

        new SapiEmitter()->emit($response);

        $this->assertSame(
            [
                'Content-Type: text/plain',
                'Set-Cookie: key-1=value-1',
                'X-Custom: value1, value2',
            ],
            HTTPFunctions::headers_list(),
        );
    }

    public function testHeaderWithResponseSeveralAddHeaderAndSetCookieNotReplaced(): void
    {
        $response = $this->createResponse(200, ['X-Test' => 'test-1'], 'Contents');

        $response = $response
            ->withAddedHeader('Set-Cookie', 'key-1=value-1')
            ->withAddedHeader('Set-Cookie', 'key-2=value-2')
        ;

        new SapiEmitter()->emit($response);

        $this->assertSame(200, Httpfunctions::http_response_code());
        $this->assertSame(
            [
                'X-Test: test-1',
                'Set-Cookie: key-1=value-1',
                'Set-Cookie: key-2=value-2',
            ],
            Httpfunctions::headers_list(),
        );
        $this->assertSame('HTTP/1.1 200 OK', $this->httpResponseStatusLine($response));
        $this->expectOutputString('Contents');
    }

    #[DataProviderExternal(EmitterProvider::class, 'noBodyStatusCodes')]
    public function testNoBodyStatusCodes(int $code, string $phrase): void
    {
        $response = $this->createResponse(
            $code,
            ['Content-Type' => 'text/plain'],
            'This content should not be emitted',
        );
        $response = $response->withStatus($code, $phrase);

        new SapiEmitter()->emit($response);

        $this->assertSame($code, HTTPFunctions::http_response_code());
        $this->assertSame(['Content-Type: text/plain'], HTTPFunctions::headers_list());
        $this->assertSame(
            "HTTP/1.1 $code $phrase",
            $this->httpResponseStatusLine($response),
        );
        $this->expectOutputString('');
    }

    public function testThrowHeadersAlreadySent(): void
    {
        HttpFunctions::set_headers_sent(true, 'file', 123);

        $this->expectException(HeadersAlreadySent::class);
        $this->expectExceptionMessage('Unable to emit response; headers already sent.');

        (new SapiEmitter())->emit($this->createResponse());
    }

    public function testThrowOutputAlreadySent(): void
    {
        $response = new Response(200, [], fopen('php://output', 'c'));
        $response->getBody()->write('Contents');

        $this->expectOutputString('Contents');

        $this->expectException(OutputAlreadySent::class);
        $this->expectExceptionMessage('Unable to emit response; output has been emitted previously.');

        (new SapiEmitter())->emit($response);
    }

    public function testValidateOutputWithNonEmptyBuffer(): void
    {
        ob_start();
        echo "buffer content";

        $this->assertSame(2, ob_get_level());
        $this->assertGreaterThan(0, ob_get_length());

        $this->expectException(OutputAlreadySent::class);

        $emitter = new SapiEmitter();
        $response = $this->createResponse();

        try {
            $emitter->emit($response);
        } finally {
            ob_end_clean();
        }
    }

    public function testValidateOutputWithZeroBufferLevel(): void
    {
        $this->expectNotToPerformAssertions();

        ob_start();
        ob_clean();

        $emitter = new SapiEmitter();
        $response = $this->createResponse();

        $emitter->emit($response);

        ob_end_clean();
    }

    private function createResponse(
        int $status = 200,
        array $headers = [],
        StreamInterface|string|null $body = null,
        string $protocolVersion = '1.1',
    ): ResponseInterface {
        $response = new Response($status, protocol: $protocolVersion);

        foreach ($headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        if ($body instanceof StreamInterface) {
            return $response->withBody($body);
        }

        if (is_string($body)) {
            $response->getBody()->write($body);
        }

        return $response;
    }

    private function httpResponseStatusLine(ResponseInterface $response): string
    {
        return match ($response->getReasonPhrase() !== '') {
            true => "HTTP/{$response->getProtocolVersion()} {$response->getStatusCode()} {$response->getReasonPhrase()}",
            default => "HTTP/{$response->getProtocolVersion()} {$response->getStatusCode()}",
        };
    }
}
