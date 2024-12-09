<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ClassWithConstructorInterface
{
    private EngineInterface $interface;

    public function __construct(EngineInterface $interface)
    {
        $this->interface = $interface;
    }

    public function getInterface(): EngineInterface
    {
        return $this->interface;
    }

    public function performActions(): array
    {
        return [
            'name' => $this->interface->getName(),
        ];
    }
}
