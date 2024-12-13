<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ConstructorVariadicWithoutTypeHint
{
    private $variadic = [];

    public function __construct(...$variadic)
    {
        $this->variadic = $variadic;
    }

    public function getConstructorArguments(): array
    {
        return ['variadic' => $this->variadic];
    }
}
