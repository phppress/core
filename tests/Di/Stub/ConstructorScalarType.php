<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final readonly class ConstructorScalarType
{
    public function __construct(
        private bool $bool,
        private int $int,
        private float $float,
        private string $string,
    ) {}

    public function getConstructorArguments(): array
    {
        return [
            'bool' => $this->bool,
            'int' => $this->int,
            'float' => $this->float,
            'string' => $this->string,
        ];
    }
}
