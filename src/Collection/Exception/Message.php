<?php

declare(strict_types=1);

namespace PHPPress\Collection\Exception;

/**
 * Represents the exception message for the Collection component.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
enum Message: string
{
    case EMPTY_STACK_FIRST_ITEM = 'Cannot get first item from empty collection.';
    case EMPTY_STACK_LAST_ITEM = 'Cannot get last item from empty collection.';
    case EMPTY_STACK_POP = 'Cannot pop item from empty collection.';
    case EMPTY_STACK_SHIFT = 'Cannot shift item from empty collection.';

    public function getMessage(): string
    {
        return match ($this) {
            self::EMPTY_STACK_FIRST_ITEM,
            self::EMPTY_STACK_LAST_ITEM,
            self::EMPTY_STACK_POP,
            self::EMPTY_STACK_SHIFT => $this->value,
        };
    }
}
