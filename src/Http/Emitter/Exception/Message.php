<?php

declare(strict_types=1);

namespace PHPPress\Http\Emitter\Exception;

/**
 * Represents the exception message for the Emitter component.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
enum Message: string
{
    case BUFFER_LENGTH_INVALID = 'Buffer length for `%s` must be greater than zero; received `%d`.';
    case UNABLE_TO_EMIT_OUTPUT_HAS_BEEN_EMITTED = 'Unable to emit response; output has been emitted previously.';
    case UNABLE_TO_EMIT_RESPONSE_HEADERS_ALREADY_SENT = 'Unable to emit response; headers already sent.';

    public function getMessage(int|string ...$argument): string
    {
        return match ($this) {
            self::UNABLE_TO_EMIT_OUTPUT_HAS_BEEN_EMITTED,
            self::UNABLE_TO_EMIT_RESPONSE_HEADERS_ALREADY_SENT => $this->value,
            self::BUFFER_LENGTH_INVALID => sprintf($this->value, ...$argument),
        };
    }
}
