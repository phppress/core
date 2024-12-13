<?php

declare(strict_types=1);

namespace PHPPress\Helper;

use function array_all;
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
     * An array is associative if all its keys are strings.
     *
     * @param array $array The array being checked.
     *
     * @return bool Whether the array is associative. That an empty array will NOT be considered associative.
     */
    public static function isAssociative(array $array): bool
    {
        if ($array === []) {
            return false;
        }

        return array_all($array, static fn(mixed $_value, int|string $key) => is_string($key));
    }
}
