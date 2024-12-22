<?php

declare(strict_types=1);

namespace PHPPress\Http\Emitter;

use function preg_match;

/**
 * Represents a Content-Range header value.
 *
 * This class parses and represents the Content-Range header used in HTTP responses for partial content delivery
 * (HTTP 206). It handles the standard format of: Content-Range: <unit> <first>-<last>/<length>
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 *
 * {@see https://tools.ietf.org/html/rfc7233#section-4.2}
 */
readonly class ContentRange
{
    /**
     * Create a new Content-Range instance.
     *
     * @param ContentRangeUnit $unit The unit of measurement (e.g., 'bytes').
     * @param int $first The first byte position in the range.
     * @param int $last The last byte position in the range.
     * @param string|int $length The total length of the resource ('*' for unknown length).
     */
    public function __construct(
        public ContentRangeUnit $unit,
        public int $first,
        public int $last,
        public string|int $length,
    ) {}

    /**
     * Create a ContentRange instance from a Content-Range header string.
     *
     * Parses a Content-Range header value in the format:
     * <unit> <first>-<last>/<length>
     * Example: "bytes 0-1233/1234" or "bytes 42-1233/*"
     *
     * @param string $header The Content-Range header value to parse.
     *
     * @return self|null Returns a ContentRange instance if parsing succeeds, `null` otherwise.
     */
    public static function fromHeader(string $header): self|null
    {
        if (preg_match('/(?P<unit>\w+)\s+(?P<first>\d+)-(?P<last>\d+)\/(?P<length>\d+|\*)/', $header, $matches)) {
            $first = (int) $matches['first'];
            $last = (int) $matches['last'];

            if ($first > $last) {
                return null;
            }

            if ($matches['unit'] !== ContentRangeUnit::BYTES->value) {
                return null;
            }

            return new self(
                ContentRangeUnit::from($matches['unit']),
                $first,
                $last,
                $matches['length'] === '*' ? '*' : (int) $matches['length'],
            );
        }

        return null;
    }

    /**
     * Convert the Content-Range to its string representation.
     *
     * @return string The string representation in format: <unit> <first>-<last>/<length>
     */
    public function __toString(): string
    {
        return "{$this->unit->value} {$this->first}-{$this->last}/{$this->length}";
    }
}
