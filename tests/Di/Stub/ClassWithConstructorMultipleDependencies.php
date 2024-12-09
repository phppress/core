<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ClassWithConstructorMultipleDependencies
{
    private EngineInterface $firstDependency;
    private ClassInterface $secondDependency;

    public function __construct(EngineInterface $firstDependency, ClassInterface $secondDependency)
    {
        $this->firstDependency = $firstDependency;
        $this->secondDependency = $secondDependency;
    }

    public function getFirstDependency(): EngineInterface
    {
        return $this->firstDependency;
    }

    public function getSecondDependency(): ClassInterface
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
