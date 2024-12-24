<?php

declare(strict_types=1);

namespace PHPPress\Middleware\Exception;

/**
 * Represents an exception caused by an unhandled request.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class UnhandledRequest extends \Exception
{
    public function __construct(string $message = '', int $code = 0, \Throwable|null $previous = null)
    {
        if ($message !== '') {
            $message = "Unhandled request: \"$message\"";
        }

        parent::__construct($message, $code, $previous);
    }
}
