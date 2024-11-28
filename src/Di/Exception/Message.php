<?php

declare(strict_types=1);

namespace PHPPress\Di\Exception;

/**
 * Represents the exception message for the Di component.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
enum Message: string
{
    case COMPONENT_ID_EMPTY = 'The required component "id" is empty.';
    case DEFINITION_INVALID = 'Invalid definition for "%s": %s';
    case DEFINITION_REQUIRES_CLASS_OPTION = 'A class definition requires a "__class" or "class" member.';
    case DEFINITION_TYPE_UNSUPPORTED = 'Unsupported definition type for "%s".';
    case DEPENDENCIES_IDX_NAME_POSITION = 'Dependencies indexed by name and by position in the same array are not allowed.';
    case INSTANTIATION_FAILED = 'Failed to instantiate component or class: "%s".';
    case PARAMETER_CALLABLE_MISSING = 'Missing required parameter "%s" when calling "%s".';
    case PARAMETER_MISSING = 'Missing required parameter "%s" when instantiating "%s".';

    public function getMessage(string ...$argument): string
    {
        return match ($this) {
            self::COMPONENT_ID_EMPTY,
            self::DEFINITION_REQUIRES_CLASS_OPTION,
            self::DEPENDENCIES_IDX_NAME_POSITION => $this->value,
            self::DEFINITION_INVALID,
            self::DEFINITION_TYPE_UNSUPPORTED,
            self::INSTANTIATION_FAILED,
            self::PARAMETER_CALLABLE_MISSING,
            self::PARAMETER_MISSING => sprintf($this->value, ...$argument),
        };
    }
}
