<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ClassWithConstructorUnionType
{
    public function __construct(protected string|int|float|bool|null $value) {}

    public function getValue(): string|int|float|bool|null
    {
        return $this->value;
    }
}