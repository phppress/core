<?php

declare(strict_types=1);

namespace PHPPress\Di;

use function substr;
use function str_ends_with;

/**
 * Provides a base implementation for the configurable object factory.
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
final class ConfigurableFactory
{
    /**
     * Configures an object class with the initial property values or methods.
     *
     * @param object $class The object class to be configured.
     * @param array $definitions The property values (name-value pairs) given in terms of property names or methods.
     *
     * @return object The object class itself.
     *
     * @phpstan-param array<string, mixed> $definitions
     */
    public static function configure(object $class, array $definitions = []): object
    {
        foreach ($definitions as $name => $value) {
            if (str_ends_with($name, '()')) {
                $nameFunction = substr($name, 0, -2);
                $setter = $class->$nameFunction(...$value);

                if ($setter instanceof $class) {
                    $class = $setter;
                }
            } else {
                $class->$name = $value;
            }
        }

        return $class;
    }
}
