<?php

declare(strict_types=1);

namespace PHPPress\Http\Emitter;

/**
 * Represents the unit of measurement used in Content-Range headers.
 *
 * Currently supports 'bytes' as the only unit as per RFC 7233, but is extensible for potential future units.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 *
 * {@see https://tools.ietf.org/html/rfc7233#section-4.2}
 */
enum ContentRangeUnit: string
{
    case BYTES = 'bytes';
}
