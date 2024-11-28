<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class Hook
{
    public string $firstName {
        set(string $name) => ucfirst($name);
    }

    public string $fullName {
        get => public $this->firstName . ' ' . public $this->lastName;
    }

    public string $lastName {
        set(string $name) => ucfirst($name);
    }
}
