<?php

declare(strict_types=1);

namespace PHPPress\Helper;

use function array_all;
use function array_any;
use function is_string;

/**
 * Provides concrete implementation for [[Arr]].
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
abstract class AbstractArray
{
    /**
     * Returns a value indicating whether the given array is a list.
     *
     * An array is a list if all keys are integers. If array is empty, it will be considered a list.
     *
     * @param array $array The array being checked.
     *
     * @return bool Whether the array is a list.
     */
    public static function isList(array $array): bool
    {
        return array_is_list($array);
    }

    /**
     * Returns a value indicating whether the given array is an associative array.
     *
     * An array is associative if all its keys are strings. If strict is true, the method will only consider an array
     * associative if its keys are all strings and not all its keys are integers.
     *
     * Note that an empty array will NOT be considered associative.
     *
     * @param array $array The array being checked.
     * @param bool $strictMode Whether to check if the array is strictly associative.
     *
     * @return bool Whether the array is associative.
     */
    public static function isAssociative(array $array, bool $strictMode = true): bool
    {
        if ($array === []) {
            return false;
        }

        if ($strictMode === false) {
            return array_any($array, static fn(mixed $_value, int|string $key) => is_string($key));
        }

        return array_all($array, static fn(mixed $_value, int|string $key) => is_string($key));
    }
}
