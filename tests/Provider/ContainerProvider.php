<?php

declare(strict_types=1);

namespace PHPPress\Tests\Provider;

use PHPPress\Tests\Di\Stub\{InstanceInterface, EngineInterface, EngineMarkOne, EngineStorage};

/**
 * Provider for the Container class.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ContainerProvider
{
    /**
     * Data provider for the has method.
     *
     * @phpstan-return array<array{bool, class-string|string}>
     */
    public static function has(): array
    {
        return [
            [false, 'non_existing'],
            [false, InstanceInterface::class],
            [true, EngineInterface::class],
            [true, EngineMarkOne::class],
            [true, EngineStorage::class],
        ];
    }
}
