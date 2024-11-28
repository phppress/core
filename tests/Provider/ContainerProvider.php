<?php

declare(strict_types=1);

namespace PHPPress\Tests\Provider;

use PHPPress\Tests\Di\Stub\{Bar, Car, ColorInterface, EngineInterface, EngineMarkOne, EngineStorage, Kappa};

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
            [false, ColorInterface::class],
            [true, Car::class],
            [true, EngineMarkOne::class],
            [true, EngineInterface::class],
            [true, EngineStorage::class],
        ];
    }

    /**
     * Data provider for the get method.
     *
     * @phpstan-return array<array{class-string, string}>
     */
    public static function notfountException(): array
    {
        return [
            [
                Bar::class,
                'Not instantiable exception: Missing required parameter "definitionInstance" when instantiating "'
                . Bar::class . '".',
            ],
            [
                Kappa::class,
                'Not instantiable exception: Missing required parameter "unknown" when instantiating "'
                . Kappa::class . '".',
            ],
        ];
    }
}
