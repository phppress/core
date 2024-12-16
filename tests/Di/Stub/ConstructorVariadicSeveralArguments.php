<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

use function array_map;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ConstructorVariadicSeveralArguments
{
    private array $variadic;

    public function __construct(
        private readonly array $array,
        private readonly object $object,
        private readonly string $string,
        EngineInterface|null ...$variadic,
    ) {
        $this->variadic = $variadic;
    }

    public function getConstructorArguments(): array
    {
        return [
            'array' => $this->array,
            'object' => $this->object,
            'string' => $this->string,
            'variadic' => array_map(
                static fn(EngineInterface|null $engine): string|null => $engine?->getName(),
                $this->variadic,
            ),
        ];
    }
}
