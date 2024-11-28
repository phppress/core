<?php

declare(strict_types=1);

namespace PHPPress\Tests\Di\Stub;

use PHPPress\Di\Container;

/**
 * Stub class for testing.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class DefinitionStaticClassFactory
{
    public static function create(Container $container): object
    {
        $definitionClass = $container->get(DefinitionClass::class);
        $definitionClass->setA(42);

        return $definitionClass;
    }
}
