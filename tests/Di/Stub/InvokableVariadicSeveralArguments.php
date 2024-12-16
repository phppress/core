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
final class InvokableVariadicSeveralArguments
{
    public function __invoke(
        array $array,
        callable $callable,
        object $object,
        string $string,
        EngineInterface|null ...$variadic,
    ): array {
        return [
            'array' => $array,
            'callable' => $callable,
            'object' => $object,
            'string' => $string,
            'variadic' => array_map(
                static fn(EngineInterface|null $engine): string|null => $engine?->getName(),
                $variadic,
            ),
        ];
    }
}
