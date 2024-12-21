<?php

declare(strict_types=1);

namespace PHPPress\Factory\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Represents an exception caused by incorrect dependency injection container configuration or usage.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class NotInstantiable extends Exception implements NotFoundExceptionInterface
{
    public function __construct(string $message = '', int $code = 0, \Throwable|null $previous = null)
    {
        if ($message !== '') {
            $message = "Not instantiable: \"$message\"";
        }

        parent::__construct($message, $code, $previous);
    }
}
