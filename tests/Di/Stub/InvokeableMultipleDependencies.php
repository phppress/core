<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class InvokeableMultipleDependencies
{
    private EngineInterface $firstDependency;
    private InstanceInterface $secondDependency;

    public function __invoke(EngineInterface $firstDependency, InstanceInterface $secondDependency): self
    {
        $this->firstDependency = $firstDependency;
        $this->secondDependency = $secondDependency;

        return $this;
    }

    public function getFirstDependency(): EngineInterface
    {
        return $this->firstDependency;
    }

    public function getSecondDependency(): InstanceInterface
    {
        return $this->secondDependency;
    }

    public function performActions(): array
    {
        return [
            'first' => $this->firstDependency->getName(),
            'second' => $this->secondDependency->getA(),
        ];
    }
}
