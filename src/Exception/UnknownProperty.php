<?php

declare(strict_types=1);

namespace PHPPress\Exception;

/**
 * Represents an exception caused by accessing unknown object properties.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class UnknownProperty extends \Exception
{
    public function __construct(string $message = '', int $code = 0, \Throwable|null $previous = null)
    {
        if ($message !== '') {
            $message = "Unknown property: \"$message\"";
        }

        parent::__construct($message, $code, $previous);
    }
}
