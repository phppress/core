<?php

declare(strict_types=1);

namespace PHPPress\Base\Exception;

/**
 * Represents the exception message for the base classes.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
enum Message: string
{
    case CALLING_UNKNOWN_METHOD = 'Calling unknown method: %s::%s().';

    public function getMessage(string ...$argument): string
    {
        return match ($this) {
            self::CALLING_UNKNOWN_METHOD => sprintf($this->value, ...$argument),
        };
    }
}
