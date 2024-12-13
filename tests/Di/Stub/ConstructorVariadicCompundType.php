<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ConstructorVariadicCompundType
{
    private array $variadic;

    public function __construct(array|callable|object|null ...$variadic)
    {
        $this->variadic = $variadic;
    }

    public function getConstructorArguments(): array
    {
        return ['variadic' => $this->variadic];
    }
}
