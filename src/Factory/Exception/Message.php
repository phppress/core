<?php

declare(strict_types=1);

namespace PHPPress\Factory\Exception;

/**
 * Represents the exception message for the Factory component.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
enum Message: string
{
    case DEFINITION_EMPTY = 'The definition is empty.';
    case DEFINITION_INVALID = 'Invalid definition for "%s": %s';
    case DEFINITION_REQUIRES_CLASS_OPTION = 'A class definition requires a "__class" or "class" member.';
    case DEFINITION_TYPE_UNSUPPORTED = 'Unsupported definition type for "%s".';
    case DEPENDENCIES_IDX_NAME_POSITION = 'Dependencies indexed by name and by position in the same array are not allowed.';
    case INSTANTIATION_FAILED = 'Failed to instantiate component or class: "%s".';
    case METHOD_NOT_ACCESSIBLE = 'Method "%s" in class "%s" is not publicly accessible.';
    case METHOD_NOT_FOUND = 'Method "%s" not found in class "%s".';
    case PARAMETER_MISSING = 'Missing required parameter "%s" when calling "%s".';

    public function getMessage(string ...$argument): string
    {
        return match ($this) {
            self::DEFINITION_EMPTY,
            self::DEFINITION_REQUIRES_CLASS_OPTION,
            self::DEPENDENCIES_IDX_NAME_POSITION => $this->value,
            self::DEFINITION_INVALID,
            self::DEFINITION_TYPE_UNSUPPORTED,
            self::INSTANTIATION_FAILED,
            self::METHOD_NOT_ACCESSIBLE,
            self::METHOD_NOT_FOUND,
            self::PARAMETER_MISSING => sprintf($this->value, ...$argument),
        };
    }
}
