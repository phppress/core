<?php

declare(strict_types=1);

namespace PHPPress\Exception;

/**
 * Represents an exception caused by incorrect object configuration.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 *
 * @see The [guide article on handling errors](guide:runtime-handling-errors)
 */
final class InvalidConfig extends \Exception
{
    public function __construct(string $message = '', int $code = 0, \Throwable|null $previous = null)
    {
        $message = 'Invalid configuration: ' . $message;

        parent::__construct($message, $code, $previous);
    }
}
