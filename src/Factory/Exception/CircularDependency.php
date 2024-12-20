<?php

declare(strict_types=1);

namespace PHPPress\Factory\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

/**
 * Represents an exception caused by circular dependency is detected during object creation.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class CircularDependency extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(string $message = '', int $code = 0, \Throwable|null $previous = null)
    {
        if ($message !== '') {
            $message = "Circular dependency exception: \"$message\"";
        }

        parent::__construct($message, $code, $previous);
    }
}
