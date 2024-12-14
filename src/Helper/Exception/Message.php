<?php

declare(strict_types=1);

namespace PHPPress\Helper\Exception;

/**
 * Represents the exception message for the helper classes.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
enum Message: string
{
    case GETTING_UNKNOWN_PROPERTY = 'Getting unknown property: %s::%s.';
    case GETTING_WRITE_ONLY_PROPERTY = 'Getting write-only property: %s::%s.';
    case SETTING_UNKNOWN_PROPERTY = 'Setting unknown property: %s::%s.';
    case SETTING_READ_ONLY_PROPERTY = 'Setting read-only property: %s::%s.';

    public function getMessage(string ...$argument): string
    {
        return match ($this) {
            self::GETTING_UNKNOWN_PROPERTY,
            self::GETTING_WRITE_ONLY_PROPERTY,
            self::SETTING_UNKNOWN_PROPERTY,
            self::SETTING_READ_ONLY_PROPERTY => sprintf($this->value, ...$argument),
        };
    }
}
