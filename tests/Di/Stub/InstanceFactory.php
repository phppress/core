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
final class InstanceFactory
{
    public static function create(ContainerInterface $container): InstanceInterface
    {
        $instance = $container->get(Instance::class);
        $instance->setA(42);

        return $instance;
    }
}
