<?php

declare(strict_types=1);

namespace PHPPress\Di\Exception;

/**
 * Represents an exception caused by incorrect dependency injection container configuration or usage.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class NotInstantiable extends \Exception
{
    public function __construct(string $message = '', int $code = 0, \Throwable|null $previous = null)
    {
        if ($message !== '') {
            $message = "Not instantiable exception: \"$message\"";
        }

        parent::__construct($message, $code, $previous);
    }
}
