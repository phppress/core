<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class InvokableOptional
{
    public function __invoke(EngineInterface|null $engine = null): EngineInterface|null
    {
        return $engine ? new EngineCar($engine)->getEngine() : null;
    }
}
