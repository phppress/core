<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ConstructorVaradicMixed
{
    private array $value = [];

    public function __construct(mixed ...$value)
    {
        $this->value = $value;
    }

    public function getValue(): array
    {
        return $this->value;
    }
}
