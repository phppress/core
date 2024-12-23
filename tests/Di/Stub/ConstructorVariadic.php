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
final class ConstructorVariadic
{
    private array $variadic;

    public function __construct(EngineInterface ...$variadic)
    {
        $this->variadic = $variadic;
    }

    public function getConstructorArguments(): array
    {
        return [
            'variadic' => array_map(
                static fn(EngineInterface $engine): string => $engine->getName(),
                $this->variadic,
            ),
        ];
    }
}
