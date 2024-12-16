<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final readonly class ConstructorDefaultValue
{
    public function __construct(
        private string               $class = 'ConstructorDefaultValue',
        private EngineInterface|null $engine = null,
    ) {}

    public function getConstructorArguments(): array
    {
        return [
            'class' => $this->class,
            'engine' => $this->engine?->getName(),
        ];
    }
}
