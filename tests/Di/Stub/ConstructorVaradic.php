<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ConstructorVaradic
{
    private array $value = [];

    public function __construct(private int $key, string ...$value)
    {
        $this->value = $value;
    }

    public function getKey(): int
    {
        return $this->key;
    }

    public function getValue(): array
    {
        return $this->value;
    }
}
