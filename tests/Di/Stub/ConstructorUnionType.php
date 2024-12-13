<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ConstructorUnionType
{
    public function __construct(protected EngineColorInterface|EngineInterface $engine) {}

    public function getConstructorArguments(): array
    {
        $color = match ($this->engine instanceof EngineColorInterface) {
            true => $this->engine->getColor(),
            default => null,
        };

        $engine = match ($this->engine instanceof EngineInterface) {
            true => $this->engine->getName(),
            default => null,
        };

        return ['color' => $color, 'engine' => $engine];
    }
}
