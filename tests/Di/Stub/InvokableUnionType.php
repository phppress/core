<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class InvokableUnionType
{
    public function __invoke(EngineColorInterface|EngineInterface $engine): string
    {
        return match ($engine instanceof EngineInterface) {
            true => $engine->getName(),
            default => $engine->getColor(),
        };
    }
}
