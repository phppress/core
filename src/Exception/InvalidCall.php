<?php

declare(strict_types=1);

namespace PHPPress\Exception;

/**
 * Represents an exception caused by calling a method in a wrong way.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class InvalidCall extends \Exception
{
    public function __construct(string $message = '', int $code = 0, \Throwable|null $previous = null)
    {
        if ($message !== '') {
            $message = "Invalid call: \"$message\"";
        }

        parent::__construct($message, $code, $previous);
    }
}
