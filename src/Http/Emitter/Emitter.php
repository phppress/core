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
 * According to HTTP/1.1 specifications, certain status codes MUST NOT include a message body:
 * - `1xx` (Informational): `100` Continue, `101` Switching Protocols, `102` Processing, `103` Early Hints.
 * - `204` No Content, `205` Reset Content.
 * - `304` Not Modified.
 *
 * Implementations of this interface must ensure that no body content is emitted for these status codes, regardless of
 * whether a body is present in the response object.
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
     * Note: According to HTTP/1.1 specifications, responses with certain status codes
     * (`100` - `103`, `204`, `205`, `304`) MUST NOT include a message body. For these status codes, the body will not
     * be emitted even if present in the response object.
     *
     * @param ResponseInterface $response The PSR-7 response to emit.
     * @param bool $body Whether to emit the response body.
     */
    public function emit(ResponseInterface $response, bool $body = false): void;
}
