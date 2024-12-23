<?php

declare(strict_types=1);

namespace PHPPress\Http\Emitter\Exception;

/**
 * Represents an exception caused by output already sent.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class OutputAlreadySent extends \RuntimeException
{
    public function __construct(string $message = '', int $code = 0, \Throwable|null $previous = null)
    {
        if ($message !== '') {
            $message = "Output already sent: \"$message\"";
        }

        parent::__construct($message, $code, $previous);
    }
}
