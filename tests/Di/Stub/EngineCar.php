<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final readonly class EngineCar
{
    public function __construct(
        private EngineInterface $engine,
        private array           $moreEngines = [],
    ) {}

    public function getEngine(): EngineInterface
    {
        return $this->engine;
    }

    public function getEngineName(): string
    {
        return $this->engine->getName();
    }

    public function getMoreEngines(): array
    {
        return $this->moreEngines;
    }
}
