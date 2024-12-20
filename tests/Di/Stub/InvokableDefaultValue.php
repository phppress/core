<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class InvokableDefaultValue
{
    public function __invoke(string $class = 'InvokableDefaultValue', EngineInterface|null $engine = null): array
    {
        return [
            'class' => $class,
            'engine' => $engine?->getName(),
        ];
    }
}
