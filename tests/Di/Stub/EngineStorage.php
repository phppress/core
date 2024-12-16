<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final readonly class EngineStorage
{
    private array $engines;

    public function __construct(EngineInterface ...$engines)
    {
        $this->engines = $engines;
    }

    public function getEngines(): array
    {
        return $this->engines;
    }
}
