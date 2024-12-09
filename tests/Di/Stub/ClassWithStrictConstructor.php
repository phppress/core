<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

use InvalidArgumentException;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ClassWithStrictConstructor
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty($value) || strlen($value) < 3) {
            throw new InvalidArgumentException(
                'Value must be a non-empty string with at least 3 characters',
            );
        }

        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
