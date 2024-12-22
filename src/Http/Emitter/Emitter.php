<?php

declare(strict_types=1);

namespace PHPPress\Http\Emitter;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface for emitting PSR-7 HTTP responses.
 *
 * Provides functionality to emit HTTP responses including status line, headers and message body according to PSR-7
 * HTTP message specifications.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
interface Emitter
{
    /**
     * Emit a response to the output buffer.
     *
     * Emits the response by sending the status line, headers and message body to output. When $body is true, only
     * headers will be emitted without the body content.
     *
     * @param ResponseInterface $response The PSR-7 response to emit.
     * @param bool $body Whether to emit the response body.
     */
    public function emit(ResponseInterface $response, bool $body = false): void;
}
