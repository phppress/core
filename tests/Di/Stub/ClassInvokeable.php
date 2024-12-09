<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

use Psr\Container\ContainerInterface;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ClassInvokeable
{
    public function __invoke(ContainerInterface $container): EngineCar
    {
        $engine = $container->get(EngineInterface::class);

        return new EngineCar($engine);
    }
}
