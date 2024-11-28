<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ConstructorNullValueArgumentDefault
{
    public function __construct(private readonly Car|null $car = null) {}

    public function getCar(): Car|null
    {
        return $this->car;
    }
}
