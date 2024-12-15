<?php

declare(strict_types=1);

namespace PHPPress\Helper;

use function str_ends_with;
use function substr;

/**
 * Provides concrete implementation for [[Configure]].
 *
 * @copyright Copyright (C) 2024 PHPPress.
 * @license GNU General Public License version 3 or later {@see LICENSE}
 */
abstract class AbstractConfigure
{
    /**
     * Configures an object class with the initial property values or methods.
     *
     * @param object $object The object class to be configured.
     * @param array $config The property values (name-value pairs) given in terms of property names or methods.
     *
     * @return object The object class itself.
     */
    public static function object(object $object, array $config = []): object
    {
        foreach ($config as $name => $value) {
            if (str_ends_with($name, '()')) {
                $nameFunction = substr($name, 0, -2);
                $setter = $object->$nameFunction(...$value);

                if ($setter instanceof $object) {
                    $object = $setter;
                }
            } else {
                $object->$name = $value;
            }
        }

        return $object;
    }
}
