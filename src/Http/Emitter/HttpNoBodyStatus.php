<?php

declare(strict_types=1);

namespace PHPPress\Http\Emitter;

/**
 * HTTP status codes that MUST NOT include a message body according to RFC 7231.
 *
 * @see https://tools.ietf.org/html/rfc7231
 */
enum HttpNoBodyStatus: int
{
    /**
     * 1xx (Informational): The request was received, continuing process.
     */
    case CONTINUE = 100;
    case SWITCHING_PROTOCOLS = 101;
    case PROCESSING = 102;
    case EARLY_HINTS = 103;

    /**
     * 2xx (Successful): The request was successfully received, understood, and accepted.
     */
    case NO_CONTENT = 204;
    case RESET_CONTENT = 205;

    /**
     * 3xx (Redirection): Further action needs to be taken in order to complete the request.
     */
    case NOT_MODIFIED = 304;

    /**
     * Check if a given status code should have no body.
     */
    public static function shouldHaveNoBody(int $statusCode): bool
    {
        return match ($statusCode) {
            self::CONTINUE->value,
            self::SWITCHING_PROTOCOLS->value,
            self::PROCESSING->value,
            self::EARLY_HINTS->value,
            self::NO_CONTENT->value,
            self::RESET_CONTENT->value,
            self::NOT_MODIFIED->value => true,
            default => false,
        };
    }
}
