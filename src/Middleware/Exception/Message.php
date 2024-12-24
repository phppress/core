<?php

declare(strict_types=1);

namespace PHPPress\Middleware\Exception;

use function sprintf;

/**
 * Represents the exception message for the Middleware component.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
enum Message: string
{
    case INVALID_RESOLVE_CALLABLE_MIDDLEWARE = 'Callable middleware must return an instance of %s. Got: %s.';
    case INVALID_RESOLVE_STRING_MIDDLEWARE = 'Middleware class "%s" must implement %s or %s.';
    case INVALID_HANDLER = 'Invalid middleware handler. Expected a string, an array, a callable, an instance of ' .
        '%s or %s, but got: %s.';
    case NO_MIDDLEWARE_HANDLED_REQUEST = 'No middleware handled the request.';

    public function getMessage(string ...$argument): string
    {
        return match ($this) {
            self::NO_MIDDLEWARE_HANDLED_REQUEST => $this->value,
            self::INVALID_RESOLVE_CALLABLE_MIDDLEWARE,
            self::INVALID_RESOLVE_STRING_MIDDLEWARE,
            self::INVALID_HANDLER => sprintf($this->value, ...$argument),
        };
    }
}
