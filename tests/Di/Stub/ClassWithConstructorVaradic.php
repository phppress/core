<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ClassWithConstructorVaradic
{
    private array $valueVaradic = [];

    public function __construct(private int $key, private array $valueArray, string ...$valueVaradic)
    {
        $this->valueVaradic = $valueVaradic;
    }

    public function getKey(): int
    {
        return $this->key;
    }

    public function getValueArray(): array
    {
        return $this->valueArray;
    }

    public function getValueVaradic(): array
    {
        return $this->valueVaradic;
    }
}
