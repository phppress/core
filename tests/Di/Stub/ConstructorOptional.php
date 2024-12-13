<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ConstructorOptional
{
    public function __construct(private EngineInterface|null $engine) {}

    public function getConstructorArguments(): array
    {
        return [
            'engine' => $this->engine?->getName(),
        ];
    }
}
