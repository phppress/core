<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class InvokableSeveralArguments
{
    public function __invoke(
        array $array,
        callable $callable,
        object $object,
        string $string,
        EngineInterface|null $engine,
    ): array {
        return [
            'array' => $array,
            'callable' => $callable,
            'object' => $object,
            'string' => $string,
            'engine' => $engine?->getName(),
        ];
    }
}
