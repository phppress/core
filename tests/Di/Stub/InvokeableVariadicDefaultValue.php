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
final class InvokeableVariadicDefaultValue
{
    public function __invoke(string $class = 'InvokeableVariadicDefaultValue', EngineInterface ...$variadic): array
    {
        return [
            'class' => $class,
            'variadic' => array_map(static fn(EngineInterface $engine): string => $engine->getName(), $variadic),
        ];
    }
}
