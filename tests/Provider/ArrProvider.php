<?php

declare(strict_types=1);

namespace PHPPress\Tests\Provider;

/**
 * Provider for the {@see Arr} class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ArrProvider
{
    /**
     * Data provider for the isList method.
     *
     * @phpstan-return array<array{array<array-key, int|string>, bool}>
     */
    public static function isList(): array
    {
        return [
            [[], true],
            [[1, 2, 3], true],
            [[1], true],
            [['name' => 1, 'value' => 'test'], false],
            [['name' => 1, 'value' => 'test', 3], false],
        ];
    }

    /**
     * Data provider for the isAssociative method.
     *
     * @phpstan-return array<array{array<array-key, int|string>, bool}>
     */
    public static function isAssociative(): array
    {
        return [
            [[], false],
            [[1, 2, 3], false],
            [[1], false],
            [['name' => 1, 'value' => 'test'], true],
            [['name' => 1, 'value' => 'test', 3], false],
        ];
    }
}
