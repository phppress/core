<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ClassWithConstructorNullValue
{
    public function __construct(private readonly EngineCar|null $car = null) {}

    public function getCar(): EngineCar|null
    {
        return $this->car;
    }
}
